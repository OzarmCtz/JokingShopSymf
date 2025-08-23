#!/bin/bash

echo "🚀 Déploiement Jo-King sur Debian/Ubuntu"
echo "==============================================="

# Couleurs
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Vérifications prérequis
echo -e "${BLUE}🔍 Vérification du système...${NC}"

# Vérifier si on est sur Debian/Ubuntu
if ! command -v apt &> /dev/null; then
    echo -e "${RED}❌ Ce script nécessite Debian/Ubuntu (apt)${NC}"
    exit 1
fi

# Vérifier les permissions sudo
if ! sudo -n true 2>/dev/null; then
    echo -e "${YELLOW}🔑 Permissions sudo requises${NC}"
    sudo echo "✅ Permissions OK"
fi

echo -e "${GREEN}✅ Système compatible${NC}"

# Mise à jour du système
echo -e "${BLUE}📦 Mise à jour du système...${NC}"
sudo apt update
sudo apt upgrade -y

# Installation des dépendances
echo -e "${BLUE}🛠️ Installation des dépendances...${NC}"
sudo apt install -y \
    curl \
    wget \
    git \
    unzip \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release

# Installation de Docker
echo -e "${BLUE}🐳 Installation de Docker...${NC}"
if ! command -v docker &> /dev/null; then
    # Ajouter la clé GPG officielle de Docker
    sudo mkdir -p /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
    
    # Ajouter le repository Docker
    echo \
        "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
        $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    
    # Si Debian
    if [ -f /etc/debian_version ]; then
        echo \
            "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
            $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    # Si Ubuntu  
    else
        echo \
            "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
            $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null
    fi
    
    sudo apt update
    sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
    
    # Ajouter l'utilisateur au groupe docker
    sudo usermod -aG docker $USER
    
    echo -e "${GREEN}✅ Docker installé${NC}"
else
    echo -e "${GREEN}✅ Docker déjà installé${NC}"
fi

# Installation de Docker Compose (standalone)
echo -e "${BLUE}🔧 Installation de Docker Compose...${NC}"
if ! command -v docker-compose &> /dev/null; then
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
    echo -e "${GREEN}✅ Docker Compose installé${NC}"
else
    echo -e "${GREEN}✅ Docker Compose déjà installé${NC}"
fi

# Démarrer Docker
echo -e "${BLUE}▶️ Démarrage de Docker...${NC}"
sudo systemctl start docker
sudo systemctl enable docker

# Configuration du projet
echo -e "${BLUE}⚙️ Configuration de l'application...${NC}"

# Créer le fichier .env.local pour la production avec MySQL
cat > symfony/.env.local << EOF
# Configuration de production
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=$(openssl rand -hex 32)

# Base de données MySQL (via Docker)
DATABASE_URL="mysql://symfony:symfony@127.0.0.1:3306/symfony"

# Email (sendmail local)
MAILER_DSN=sendmail://default

# Stripe (remplacer par vos vraies clés)
STRIPE_PUBLIC_KEY=pk_test_your_public_key_here
STRIPE_SECRET_KEY=sk_test_your_secret_key_here
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret_here
EOF

echo -e "${GREEN}✅ Configuration créée${NC}"

# Construire et démarrer les conteneurs
echo -e "${BLUE}🏗️ Construction des conteneurs...${NC}"
docker-compose build --no-cache

echo -e "${BLUE}🚀 Démarrage de l'application...${NC}"
docker-compose up -d

# Attendre que les conteneurs soient prêts
echo -e "${YELLOW}⏳ Attente du démarrage des services...${NC}"
sleep 10

# Installation des dépendances Composer
echo -e "${BLUE}📦 Installation des dépendances...${NC}"
docker-compose exec -T php composer install --no-dev --optimize-autoloader --no-interaction

# Configuration de la base de données
echo -e "${BLUE}🗄️ Configuration de la base de données MySQL...${NC}"
docker-compose exec -T php php bin/console doctrine:database:create --if-not-exists --no-interaction
docker-compose exec -T php php bin/console doctrine:migrations:migrate --no-interaction

# Charger les données avec les fixtures Doctrine (méthode professionnelle)
echo -e "${BLUE}📊 Chargement des données (fixtures)...${NC}"
docker-compose exec -T php php bin/console doctrine:fixtures:load --no-interaction

# Permissions
echo -e "${BLUE}🔐 Configuration des permissions...${NC}"
docker-compose exec -T php chown -R www-data:www-data /var/www/html/var /var/www/html/public
docker-compose exec -T php chmod -R 775 /var/www/html/var

# Cache de production
echo -e "${BLUE}⚡ Optimisation du cache...${NC}"
docker-compose exec -T php php bin/console cache:clear --env=prod --no-interaction
docker-compose exec -T php php bin/console cache:warmup --env=prod --no-interaction

# Obtenir l'IP du serveur
SERVER_IP=$(hostname -I | awk '{print $1}')

echo ""
echo -e "${GREEN}🎉 DÉPLOIEMENT TERMINÉ !${NC}"
echo "================================"
echo -e "${BLUE}📍 IP du serveur:${NC} $SERVER_IP"
echo -e "${BLUE}🌐 Site web:${NC} http://$SERVER_IP:8080"
echo -e "${BLUE}🔧 Administration:${NC} http://$SERVER_IP:8080/admin"
echo -e "${BLUE}📧 Test emails:${NC} http://$SERVER_IP:8025"
echo ""
echo -e "${YELLOW}💡 Commandes utiles:${NC}"
echo "   docker-compose logs -f       # Voir les logs"
echo "   docker-compose restart       # Redémarrer"
echo "   docker-compose down          # Arrêter"
echo ""
echo -e "${YELLOW}🔑 Pour créer un admin:${NC}"
echo "   docker-compose exec php php bin/console"
echo ""

# Test de connectivité
echo -e "${BLUE}🔍 Test de connectivité...${NC}"
sleep 5

if curl -s --connect-timeout 10 http://localhost:8080 > /dev/null; then
    echo -e "${GREEN}✅ Site accessible et fonctionnel !${NC}"
else
    echo -e "${YELLOW}⏳ Site encore en démarrage (attendez 1-2 minutes)${NC}"
fi

echo ""
echo -e "${GREEN}🚀 Votre blog MyBlogSymfony est maintenant en ligne !${NC}"
