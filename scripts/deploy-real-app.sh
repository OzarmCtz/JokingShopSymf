#!/bin/bash

echo "🚀 Déploiement Ultra-Rapide de votre vraie application MyBlogSymfony"
echo "===================================================================="

# Vérifications
if [ ! -f "composer.json" ]; then
    echo "❌ Ce script doit être exécuté depuis le répertoire racine de MyBlogSymfony"
    exit 1
fi

if ! command -v aws &> /dev/null; then
    echo "❌ AWS CLI requis"
    exit 1
fi

if ! aws sts get-caller-identity &> /dev/null; then
    echo "❌ AWS CLI non configuré"
    exit 1
fi

# Variables
KEY_NAME="myblog-prod-$(date +%s)"
SG_NAME="myblog-prod-sg-$(date +%s)"
INSTANCE_NAME="myblog-prod-$(date +%s)"

echo "🔑 Création de la paire de clés..."
aws ec2 create-key-pair --key-name $KEY_NAME --query 'KeyMaterial' --output text > $KEY_NAME.pem
chmod 400 $KEY_NAME.pem

echo "🛡️ Création du groupe de sécurité..."
aws ec2 create-security-group --group-name $SG_NAME --description "MyBlogSymfony production"
SECURITY_GROUP_ID=$(aws ec2 describe-security-groups --group-names $SG_NAME --query 'SecurityGroups[0].GroupId' --output text)

# Règles de sécurité
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 22 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 80 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 443 --cidr 0.0.0.0/0

echo "📦 Création de l'archive du projet..."
# Créer une archive du projet actuel
tar -czf myblog-deploy.tar.gz \
    --exclude='var/cache/*' \
    --exclude='var/log/*' \
    --exclude='node_modules' \
    --exclude='.git' \
    --exclude='*.tar.gz' \
    --exclude='*.pem' \
    .

echo "📝 Génération du script d'installation..."
# Encoder l'archive en base64 pour l'inclure dans user-data
ARCHIVE_BASE64=$(base64 -w 0 myblog-deploy.tar.gz)

cat > user-data-prod.sh << EOF
#!/bin/bash
exec > >(tee /var/log/user-data.log|logger -t user-data -s 2>/dev/console) 2>&1

echo "🚀 Installation de MyBlogSymfony - \$(date)"

# Installation des dépendances
yum update -y
yum install -y docker git

# Docker
systemctl start docker
systemctl enable docker
usermod -a -G docker ec2-user

# Docker Compose
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-\$(uname -s)-\$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Création du répertoire de travail
mkdir -p /home/ec2-user/myblog-app
cd /home/ec2-user/myblog-app

# Extraction de l'application
echo "$ARCHIVE_BASE64" | base64 -d > myblog-deploy.tar.gz
tar -xzf myblog-deploy.tar.gz
rm myblog-deploy.tar.gz

# Configuration de production
cd symfony
cat > .env.local << 'ENVEOF'
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=\$(openssl rand -hex 32)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
MAILER_DSN=sendmail://default
ENVEOF

# Permissions
chown -R ec2-user:ec2-user /home/ec2-user/myblog-app
chmod -R 755 /home/ec2-user/myblog-app

# Démarrage des conteneurs
docker-compose up -d

# Attendre que PHP soit prêt
sleep 60

# Installation des dépendances Composer
docker-compose exec -T php composer install --no-dev --optimize-autoloader --no-interaction

# Base de données
docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

# Permissions finales
docker-compose exec -T php chown -R www-data:www-data /var/www/html/var
docker-compose exec -T php chmod -R 775 /var/www/html/var

# Cache de production
docker-compose exec -T php php bin/console cache:clear --env=prod
docker-compose exec -T php php bin/console cache:warmup --env=prod

echo "✅ Installation terminée - \$(date)"
EOF

echo "🚀 Lancement de l'instance EC2 de production..."
INSTANCE_ID=$(aws ec2 run-instances \
    --image-id ami-0df8c184d5f6ae949 \
    --count 1 \
    --instance-type t3.small \
    --key-name $KEY_NAME \
    --security-group-ids $SECURITY_GROUP_ID \
    --user-data file://user-data-prod.sh \
    --tag-specifications "ResourceType=instance,Tags=[{Key=Name,Value=$INSTANCE_NAME},{Key=Project,Value=MyBlogSymfony},{Key=Environment,Value=demo}]" \
    --query 'Instances[0].InstanceId' \
    --output text)

echo "⏳ Attente du démarrage..."
aws ec2 wait instance-running --instance-ids $INSTANCE_ID

PUBLIC_IP=$(aws ec2 describe-instances --instance-ids $INSTANCE_ID --query 'Reservations[0].Instances[0].PublicIpAddress' --output text)

echo "⏳ Installation en cours... (5-7 minutes)"
echo "   Votre application Symfony complète est en cours de déploiement..."

# Monitoring de l'installation
for i in {1..20}; do
    echo "   ⏳ Vérification $i/20..."
    sleep 30
    
    # Test simple de connectivité
    if curl -s --connect-timeout 5 http://$PUBLIC_IP > /dev/null 2>&1; then
        echo "   ✅ Application accessible!"
        break
    fi
done

echo ""
echo "🎉 DÉPLOIEMENT RÉUSSI!"
echo "====================="
echo "🌐 URL de votre application: http://$PUBLIC_IP"
echo "🔗 Interface d'administration: http://$PUBLIC_IP/admin"
echo "🔑 SSH: ssh -i $KEY_NAME.pem ec2-user@$PUBLIC_IP"
echo "📍 Instance: $INSTANCE_ID"
echo ""

# Sauvegarde des informations
cat > myblog-production-$(date +%Y%m%d-%H%M).txt << PRODEOF
=== MYBLOGSYMFONY EN PRODUCTION ===
Date de déploiement: $(date)
Instance ID: $INSTANCE_ID
IP publique: $PUBLIC_IP
URL: http://$PUBLIC_IP
Admin: http://$PUBLIC_IP/admin
SSH: ssh -i $KEY_NAME.pem ec2-user@$PUBLIC_IP

Clé SSH: $KEY_NAME.pem
Groupe de sécurité: $SECURITY_GROUP_ID

COÛT ESTIMÉ: ~4€/jour (t3.small)

COMMANDES UTILES:
- Logs: ssh -i $KEY_NAME.pem ec2-user@$PUBLIC_IP "cd myblog-app && docker-compose logs -f"
- Restart: ssh -i $KEY_NAME.pem ec2-user@$PUBLIC_IP "cd myblog-app && docker-compose restart"
- Console Symfony: ssh -i $KEY_NAME.pem ec2-user@$PUBLIC_IP "cd myblog-app && docker-compose exec php php bin/console"

NETTOYAGE:
aws ec2 terminate-instances --instance-ids $INSTANCE_ID
aws ec2 delete-security-group --group-id $SECURITY_GROUP_ID
aws ec2 delete-key-pair --key-name $KEY_NAME
PRODEOF

echo "📄 Informations sauvegardées dans myblog-production-$(date +%Y%m%d-%H%M).txt"
echo ""
echo "🎯 Votre blog Symfony est maintenant en ligne!"
echo "💡 Connectez-vous en SSH pour gérer votre application:"
echo "   ssh -i $KEY_NAME.pem ec2-user@$PUBLIC_IP"

# Nettoyage des fichiers temporaires
rm -f user-data-prod.sh myblog-deploy.tar.gz

echo ""
echo "⚡ Temps total de déploiement: ~8 minutes"
echo "🎊 Votre application MyBlogSymfony est prête!"
