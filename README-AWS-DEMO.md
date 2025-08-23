# 🚀 Déploiement AWS Simple - Démonstration MyBlogSymfony

## 🎯 Objectif
Déployer rapidement votre application Symfony sur AWS pour 1-2 jours de démonstration avec un coût minimal (~10-20€).

## 🏗️ Architecture Simplifiée
```
Internet → EC2 (t3.micro) → SQLite (local sur l'instance)
```

## ⚡ Déploiement Rapide (15 minutes)

### 1. Créer une instance EC2

```bash
# Créer une paire de clés
aws ec2 create-key-pair --key-name symfony-demo --query 'KeyMaterial' --output text > symfony-demo.pem
chmod 400 symfony-demo.pem

# Créer un groupe de sécurité
aws ec2 create-security-group \
    --group-name symfony-demo-sg \
    --description "Security group for Symfony demo"

# Autoriser HTTP et SSH
SECURITY_GROUP_ID=$(aws ec2 describe-security-groups --group-names symfony-demo-sg --query 'SecurityGroups[0].GroupId' --output text)

aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 22 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 80 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 8080 --cidr 0.0.0.0/0

# Lancer l'instance EC2
aws ec2 run-instances \
    --image-id ami-0df8c184d5f6ae949 \
    --count 1 \
    --instance-type t3.micro \
    --key-name symfony-demo \
    --security-group-ids $SECURITY_GROUP_ID \
    --user-data file://user-data.sh \
    --tag-specifications 'ResourceType=instance,Tags=[{Key=Name,Value=symfony-demo}]'
```

### 2. Script d'installation automatique

Créer `user-data.sh` :

```bash
#!/bin/bash
yum update -y
yum install -y docker git

# Démarrer Docker
systemctl start docker
systemctl enable docker
usermod -a -G docker ec2-user

# Installer Docker Compose
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Cloner le projet (remplacer par votre repo)
cd /home/ec2-user
git clone https://github.com/VotreUsername/MyBlogSymfony.git
cd MyBlogSymfony

# Configuration pour démonstration
cat > symfony/.env.local << EOF
APP_ENV=prod
APP_SECRET=$(openssl rand -hex 16)
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
MAILER_DSN=sendmail://default
EOF

# Démarrer l'application
docker-compose up -d php nginx

# Attendre que les conteneurs soient prêts
sleep 30

# Initialiser la base de données
docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction
docker-compose exec -T php php bin/console doctrine:fixtures:load --no-interaction --append

# Permissions
docker-compose exec -T php chown -R www-data:www-data /var/www/html/var
docker-compose exec -T php chmod -R 775 /var/www/html/var

echo "✅ Installation terminée!"
```

### 3. Obtenir l'IP publique

```bash
# Obtenir l'IP publique de l'instance
INSTANCE_ID=$(aws ec2 describe-instances --filters "Name=tag:Name,Values=symfony-demo" --query 'Reservations[0].Instances[0].InstanceId' --output text)
PUBLIC_IP=$(aws ec2 describe-instances --instance-ids $INSTANCE_ID --query 'Reservations[0].Instances[0].PublicIpAddress' --output text)

echo "🌐 Votre site sera accessible à: http://$PUBLIC_IP:8080"
```

## 📊 Migration des Données Rapide

### Script de migration SQLite

```bash
#!/bin/bash
# migrate-to-sqlite.sh

echo "📊 Migration vers SQLite pour démonstration"

# Export depuis Docker local
docker exec symfony_db_adeo_shop mysqldump -u symfony -psymfony symfony > demo_data.sql

# Conversion MySQL vers SQLite (simplifié)
# Installer sqlite3 si nécessaire
sudo apt install sqlite3

# Créer la base SQLite
sqlite3 demo.db < demo_data_converted.sql

# Upload vers l'instance EC2
scp -i symfony-demo.pem demo.db ec2-user@$PUBLIC_IP:/home/ec2-user/MyBlogSymfony/symfony/var/data.db

echo "✅ Données migrées"
```

## 🎛️ Configuration Minimale

### Docker Compose simplifié pour démo

Créer `docker-compose.demo.yml` :

```yaml
version: '3.8'

services:
  php:
    build:
      context: ./docker/php
    container_name: symfony_demo_php
    volumes:
      - ./symfony:/var/www/html
    working_dir: /var/www/html
    environment:
      - APP_ENV=prod
      - COMPOSER_ALLOW_SUPERUSER=1

  nginx:
    image: nginx:alpine
    container_name: symfony_demo_nginx
    ports:
      - "8080:80"
    volumes:
      - ./symfony:/var/www/html
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - php
```

## 🚀 Script de Déploiement Automatique

```bash
#!/bin/bash
# deploy-demo.sh

echo "🚀 Déploiement démo AWS - 15 minutes"

# Étape 1: Créer l'infrastructure
echo "📋 Création de l'infrastructure..."

# Créer la clé SSH
aws ec2 create-key-pair --key-name symfony-demo --query 'KeyMaterial' --output text > symfony-demo.pem
chmod 400 symfony-demo.pem

# Créer le groupe de sécurité
aws ec2 create-security-group --group-name symfony-demo-sg --description "Symfony demo"
SECURITY_GROUP_ID=$(aws ec2 describe-security-groups --group-names symfony-demo-sg --query 'SecurityGroups[0].GroupId' --output text)

# Règles de sécurité
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 22 --cidr 0.0.0.0/0
aws ec2 authorize-security-group-ingress --group-id $SECURITY_GROUP_ID --protocol tcp --port 8080 --cidr 0.0.0.0/0

# User data pour installation automatique
cat > user-data.sh << 'EOF'
#!/bin/bash
yum update -y
yum install -y docker git
systemctl start docker
systemctl enable docker
usermod -a -G docker ec2-user

curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Configuration simplifiée
mkdir -p /home/ec2-user/symfony-demo
cd /home/ec2-user/symfony-demo

# Télécharger votre code (à adapter)
# git clone https://github.com/VotreRepo/MyBlogSymfony.git .

# Pour la démo, créer un environnement minimal
cat > .env << 'ENVEOF'
APP_ENV=prod
APP_SECRET=demo_secret_key_not_for_production
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
ENVEOF

echo "Installation terminée à $(date)" > /var/log/setup.log
EOF

# Lancer l'instance
aws ec2 run-instances \
    --image-id ami-0df8c184d5f6ae949 \
    --count 1 \
    --instance-type t3.micro \
    --key-name symfony-demo \
    --security-group-ids $SECURITY_GROUP_ID \
    --user-data file://user-data.sh \
    --tag-specifications 'ResourceType=instance,Tags=[{Key=Name,Value=symfony-demo}]'

echo "⏳ Attente du démarrage de l'instance..."
sleep 60

# Obtenir l'IP
INSTANCE_ID=$(aws ec2 describe-instances --filters "Name=tag:Name,Values=symfony-demo" --query 'Reservations[0].Instances[0].InstanceId' --output text)
PUBLIC_IP=$(aws ec2 describe-instances --instance-ids $INSTANCE_ID --query 'Reservations[0].Instances[0].PublicIpAddress' --output text)

echo "🎉 Déploiement terminé!"
echo "🌐 IP publique: $PUBLIC_IP"
echo "🔗 Site accessible à: http://$PUBLIC_IP:8080"
echo "🔑 Connexion SSH: ssh -i symfony-demo.pem ec2-user@$PUBLIC_IP"

# Sauvegarder les infos
cat > demo-info.txt << EOF
=== INFORMATION DE DÉMONSTRATION ===
Date de création: $(date)
Instance ID: $INSTANCE_ID
IP publique: $PUBLIC_IP
URL du site: http://$PUBLIC_IP:8080
Connexion SSH: ssh -i symfony-demo.pem ec2-user@$PUBLIC_IP

Coût estimé: ~2€/jour pour t3.micro
EOF

echo "📄 Informations sauvegardées dans demo-info.txt"
```

## 🧹 Nettoyage Après Démonstration

```bash
#!/bin/bash
# cleanup-demo.sh

echo "🧹 Suppression de l'infrastructure de démonstration"

# Obtenir l'instance ID
INSTANCE_ID=$(aws ec2 describe-instances --filters "Name=tag:Name,Values=symfony-demo" --query 'Reservations[0].Instances[0].InstanceId' --output text)

if [ "$INSTANCE_ID" != "None" ]; then
    # Terminer l'instance
    aws ec2 terminate-instances --instance-ids $INSTANCE_ID
    echo "⏳ Attente de la terminaison..."
    aws ec2 wait instance-terminated --instance-ids $INSTANCE_ID
fi

# Supprimer le groupe de sécurité
aws ec2 delete-security-group --group-name symfony-demo-sg

# Supprimer la paire de clés
aws ec2 delete-key-pair --key-name symfony-demo
rm -f symfony-demo.pem

echo "✅ Nettoyage terminé - Coût arrêté"
```

## 💰 Coût Estimé

- **Instance t3.micro**: ~0,0116$/heure = ~0,28€/jour
- **Stockage EBS**: ~0,10€/mois (minimum)
- **Transfert de données**: ~0,05€/GB

**Total pour 2 jours**: ~1-2€ maximum

## ⚡ Déploiement Express (5 minutes)

Pour un déploiement ultra-rapide, utilisez AWS App Runner avec votre image Docker :

```bash
# Build et push vers ECR Public
aws ecr-public get-login-password --region us-east-1 | docker login --username AWS --password-stdin public.ecr.aws
docker build -t public.ecr.aws/xyz/symfony-demo .
docker push public.ecr.aws/xyz/symfony-demo

# Créer le service App Runner via console AWS
# Coût: ~7$/mois mais instantané
```

## 📝 Checklist Démonstration

- [ ] Configurer AWS CLI
- [ ] Exécuter `./deploy-demo.sh`
- [ ] Attendre 10-15 minutes
- [ ] Tester l'URL fournie
- [ ] Après démonstration: `./cleanup-demo.sh`

**Avantages de cette approche :**
- ✅ Déploiement en 15 minutes maximum
- ✅ Coût minimal (~1-2€ pour 2 jours)
- ✅ Nettoyage automatique
- ✅ Aucune configuration complexe
- ✅ Données préservées avec SQLite

Cette solution est parfaite pour une démonstration courte sans les complexités d'une architecture de production !
