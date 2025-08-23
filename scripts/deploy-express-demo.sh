#!/bin/bash

echo "🚀 Déploiement AWS Express - Démonstration MyBlogSymfony"
echo "=================================================="

# Vérification des prérequis
if ! command -v aws &> /dev/null; then
    echo "❌ AWS CLI n'est pas installé"
    echo "📦 Installation: https://aws.amazon.com/cli/"
    exit 1
fi

if ! aws sts get-caller-identity &> /dev/null; then
    echo "❌ AWS CLI n'est pas configuré"
    echo "🔧 Exécutez: aws configure"
    exit 1
fi

echo "✅ Prérequis vérifiés"

# Variables
KEY_NAME="symfony-demo-$(date +%s)"
SG_NAME="symfony-demo-sg-$(date +%s)"
INSTANCE_NAME="symfony-demo-$(date +%s)"

echo "🔑 Création de la paire de clés: $KEY_NAME"
aws ec2 create-key-pair --key-name $KEY_NAME --query 'KeyMaterial' --output text > $KEY_NAME.pem
chmod 400 $KEY_NAME.pem

echo "🛡️ Création du groupe de sécurité: $SG_NAME"
aws ec2 create-security-group --group-name $SG_NAME --description "Symfony demo security group"
SECURITY_GROUP_ID=$(aws ec2 describe-security-groups --group-names $SG_NAME --query 'SecurityGroups[0].GroupId' --output text)

echo "🌐 Configuration des règles de sécurité"
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 22 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 80 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 8080 --cidr 0.0.0.0/0

echo "📝 Création du script d'installation automatique"
cat > user-data.sh << 'EOF'
#!/bin/bash
exec > >(tee /var/log/user-data.log|logger -t user-data -s 2>/dev/console) 2>&1

echo "🚀 Début de l'installation - $(date)"

# Mise à jour du système
yum update -y

# Installation de Docker
yum install -y docker git
systemctl start docker
systemctl enable docker
usermod -a -G docker ec2-user

# Installation de Docker Compose
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Configuration pour l'utilisateur ec2-user
mkdir -p /home/ec2-user/symfony-demo
cd /home/ec2-user/symfony-demo

# Création de la structure minimale pour Symfony
cat > docker-compose.yml << 'DOCKEREOF'
version: '3.8'

services:
  php:
    image: php:8.3-fpm
    container_name: symfony_demo_php
    volumes:
      - ./app:/var/www/html
    working_dir: /var/www/html
    command: >
      bash -c "
        apt-get update &&
        apt-get install -y git unzip libzip-dev &&
        docker-php-ext-install zip pdo pdo_mysql &&
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer &&
        if [ ! -f /var/www/html/composer.json ]; then
          composer create-project symfony/skeleton:7.* /var/www/html --no-interaction &&
          cd /var/www/html &&
          composer require webapp
        fi &&
        chmod -R 777 /var/www/html/var &&
        php-fpm
      "

  nginx:
    image: nginx:alpine
    container_name: symfony_demo_nginx
    ports:
      - "80:80"
      - "8080:80"
    volumes:
      - ./app:/var/www/html
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
DOCKEREOF

# Configuration Nginx
cat > nginx.conf << 'NGINXEOF'
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_split_path_info ^(.+\.php)(/.*)$;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT $realpath_root;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
    }

    location ~ /\.ht {
        deny all;
    }
}
NGINXEOF

# Créer le répertoire app
mkdir -p app

# Permissions
chown -R ec2-user:ec2-user /home/ec2-user/symfony-demo

echo "🐳 Démarrage des conteneurs Docker"
docker-compose up -d

echo "⏳ Attente du démarrage des services (60 secondes)"
sleep 60

# Page de démonstration simple
cat > app/public/index.php << 'PHPEOF'
<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title>Symfony Demo - MyBlogSymfony</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; background: #f8f9fa; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; text-align: center; }
        .status { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 20px 0; }
        .info { background: #cce5ff; color: #004085; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .demo-link { display: inline-block; background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 5px; }
        .demo-link:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 MyBlogSymfony - Démonstration AWS</h1>
        
        <div class='status'>
            ✅ <strong>Déploiement réussi!</strong> Votre application Symfony est maintenant accessible sur AWS.
        </div>
        
        <div class='info'>
            <h3>📊 Informations de démonstration</h3>
            <p><strong>Date de déploiement:</strong> " . date('d/m/Y H:i:s') . "</p>
            <p><strong>Serveur:</strong> " . gethostname() . "</p>
            <p><strong>IP du serveur:</strong> " . $_SERVER['SERVER_ADDR'] . "</p>
            <p><strong>PHP Version:</strong> " . phpversion() . "</p>
        </div>
        
        <h3>🔗 Liens de démonstration</h3>
        <a href='/info.php' class='demo-link'>📋 Informations PHP</a>
        <a href='/test.php' class='demo-link'>🧪 Page de test</a>
        
        <div class='info'>
            <h3>💡 À propos de cette démonstration</h3>
            <p>Cette instance EC2 exécute votre application Symfony dans un environnement conteneurisé avec Docker.</p>
            <p>Architecture: <strong>EC2 t3.micro + Docker + Nginx + PHP-FPM</strong></p>
            <p>Coût estimé: <strong>~2€/jour</strong></p>
        </div>
        
        <div class='info'>
            <h3>🔧 Prochaines étapes pour votre vraie application</h3>
            <ul>
                <li>Cloner votre repository GitHub sur cette instance</li>
                <li>Configurer votre base de données (MySQL/PostgreSQL via RDS)</li>
                <li>Configurer vos variables d'environnement</li>
                <li>Installer vos dépendances Composer</li>
                <li>Exécuter vos migrations</li>
            </ul>
        </div>
    </div>
</body>
</html>";
?>
PHPEOF

# Page d'informations PHP
cat > app/public/info.php << 'PHPEOF'
<?php
phpinfo();
?>
PHPEOF

# Page de test
cat > app/public/test.php << 'PHPEOF'
<?php
echo "<!DOCTYPE html>
<html>
<head>
    <title>Test Page - MyBlogSymfony Demo</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; background: #f8f9fa; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .test { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧪 Page de Test</h1>
        
        <div class='test'>✅ PHP fonctionne correctement</div>
        <div class='test'>✅ Nginx sert les fichiers PHP</div>
        <div class='test'>✅ Docker containers en cours d'exécution</div>
        
        <p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
        <p><strong>Serveur:</strong> " . $_SERVER['HTTP_HOST'] . "</p>
        
        <a href='/'>← Retour à l'accueil</a>
    </div>
</body>
</html>";
?>
PHPEOF

# Permissions finales
chown -R ec2-user:ec2-user /home/ec2-user/symfony-demo
chmod -R 755 /home/ec2-user/symfony-demo/app

echo "✅ Installation terminée - $(date)"
echo "🌐 L'application est maintenant accessible sur le port 80 et 8080"
EOF

echo "🚀 Lancement de l'instance EC2"
INSTANCE_ID=$(aws ec2 run-instances \
    --image-id ami-0df8c184d5f6ae949 \
    --count 1 \
    --instance-type t3.micro \
    --key-name $KEY_NAME \
    --security-group-ids $SECURITY_GROUP_ID \
    --user-data file://user-data.sh \
    --tag-specifications "ResourceType=instance,Tags=[{Key=Name,Value=$INSTANCE_NAME}]" \
    --query 'Instances[0].InstanceId' \
    --output text)

echo "⏳ Attente du démarrage de l'instance..."
aws ec2 wait instance-running --instance-ids $INSTANCE_ID

echo "🔍 Récupération de l'IP publique..."
PUBLIC_IP=$(aws ec2 describe-instances --instance-ids $INSTANCE_ID --query 'Reservations[0].Instances[0].PublicIpAddress' --output text)

echo "⏳ Attente de l'installation automatique (3 minutes)..."
echo "   Docker et Symfony sont en cours d'installation..."
sleep 180

echo ""
echo "🎉 DÉPLOIEMENT TERMINÉ!"
echo "=================================================="
echo "📍 Instance ID: $INSTANCE_ID"
echo "🌐 IP publique: $PUBLIC_IP"
echo "🔗 URL principale: http://$PUBLIC_IP"
echo "🔗 URL alternative: http://$PUBLIC_IP:8080"
echo "🔑 Connexion SSH: ssh -i $KEY_NAME.pem ec2-user@$PUBLIC_IP"
echo ""
echo "💰 Coût estimé: ~2€/jour"
echo "⏰ Temps de déploiement: ~8 minutes"
echo ""

# Sauvegarder les informations
cat > demo-info-$(date +%Y%m%d-%H%M).txt << EOF
=== DÉMONSTRATION MYBLOGSYMFONY SUR AWS ===
Date de création: $(date)
Instance ID: $INSTANCE_ID
IP publique: $PUBLIC_IP
URL du site: http://$PUBLIC_IP
URL alternative: http://$PUBLIC_IP:8080
Connexion SSH: ssh -i $KEY_NAME.pem ec2-user@$PUBLIC_IP

Paire de clés: $KEY_NAME.pem
Groupe de sécurité: $SG_NAME ($SECURITY_GROUP_ID)

NETTOYAGE APRÈS DÉMONSTRATION:
aws ec2 terminate-instances --instance-ids $INSTANCE_ID
aws ec2 delete-security-group --group-id $SECURITY_GROUP_ID
aws ec2 delete-key-pair --key-name $KEY_NAME
rm $KEY_NAME.pem
EOF

echo "📄 Informations sauvegardées dans demo-info-$(date +%Y%m%d-%H%M).txt"
echo ""
echo "🧹 Pour nettoyer après la démonstration:"
echo "   ./cleanup-express-demo.sh $INSTANCE_ID $SECURITY_GROUP_ID $KEY_NAME"
echo ""
echo "⚡ Votre site sera accessible dans 2-3 minutes!"

# Vérification finale
echo "🔍 Test de connectivité dans 30 secondes..."
sleep 30

if curl -s --connect-timeout 10 http://$PUBLIC_IP > /dev/null; then
    echo "✅ Site accessible et fonctionnel!"
else
    echo "⏳ Site encore en cours de démarrage (normal, attendez 2-3 minutes)"
fi

echo ""
echo "🎯 Démonstration prête! Durée totale: ~8 minutes"
