#!/usr/bin/env bash
set -Eeuo pipefail

echo "🚀 Déploiement Jo-King sur Debian"
echo "==============================================="

# Couleurs
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'

# Répertoire projet = parent du dossier où se trouve ce script
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(realpath "${SCRIPT_DIR}/..")"
cd "$PROJECT_ROOT"

echo -e "${BLUE}🔍 Vérification du système...${NC}"
if ! command -v apt &>/dev/null; then
  echo -e "${RED}❌ Ce script nécessite Debian (apt).${NC}"; exit 1
fi
if ! sudo -n true 2>/dev/null; then
  echo -e "${YELLOW}🔑 Sudo requis (on va te le demander au besoin).${NC}"
fi
echo -e "${GREEN}✅ Système compatible${NC}"

echo -e "${BLUE}📦 Mise à jour de l’index APT...${NC}"
sudo apt update -y

echo -e "${BLUE}🛠️ Installation des prérequis...${NC}"
sudo apt install -y ca-certificates curl gnupg lsb-release git unzip

echo -e "${BLUE}🐳 Installation/Correction Docker Engine...${NC}"
# Nettoyage d’anciens dépôts Docker si présents (évite les erreurs de clé)
sudo rm -f /etc/apt/sources.list.d/docker.list /etc/apt/keyrings/docker.gpg || true
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# ⚠️ Docker ne publie pas toujours "trixie" immédiatement → on pointe sur bookworm (stable) qui fonctionne très bien
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian bookworm stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list >/dev/null

sudo apt update -y
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Démarrer & activer
sudo systemctl enable --now docker

# Ajouter l'utilisateur courant au groupe docker (peut nécessiter une reconnexion pour prendre effet)
TARGET_USER="${SUDO_USER:-$USER}"
sudo groupadd -f docker
sudo usermod -aG docker "$TARGET_USER"

# Choisir la commande compose (plugin v2 par défaut)
if docker compose version >/dev/null 2>&1; then
  DC="docker compose"
elif command -v docker-compose >/dev/null 2>&1; then
  DC="docker-compose"
else
  echo -e "${RED}❌ Docker Compose introuvable.${NC}"; exit 1
fi

echo -e "${GREEN}✅ Docker installé (${YELLOW}$($DC version 2>/dev/null || docker-compose --version)${GREEN})${NC}"

echo -e "${BLUE}⚙️ Configuration de l'application (.env.local)...${NC}"
APP_SECRET="$(openssl rand -hex 16)"

# IMPORTANT : on écrit le .env.local à la racine du projet (visible par le conteneur PHP)
cat > .env.local <<EOF
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=${APP_SECRET}

# Base de données (hôte = service Docker "db")
DATABASE_URL="mysql://symfony:symfony@db:3306/symfony"

# Mail (Mailpit dans Docker)
MAILER_DSN="smtp://mailpit:1025"

# (Placeholders Stripe)
STRIPE_PUBLIC_KEY=pk_test_change_me
STRIPE_SECRET_KEY=sk_test_change_me
STRIPE_WEBHOOK_SECRET=whsec_change_me
EOF
echo -e "${GREEN}✅ Fichier ${YELLOW}.env.local${GREEN} créé${NC}"

echo -e "${BLUE}🏗️ Build des conteneurs...${NC}"
sudo $DC build

echo -e "${BLUE}🚀 Démarrage des services...${NC}"
sudo $DC up -d

echo -e "${YELLOW}⏳ Attente du démarrage (DB/PHFPM)...${NC}"
sleep 10

echo -e "${BLUE}📦 composer install (prod)...${NC}"
sudo $DC exec -T php composer install --no-dev --optimize-autoloader --no-interaction || true

echo -e "${BLUE}🗄️ Migrations Doctrine...${NC}"
sudo $DC exec -T php php bin/console doctrine:database:create --if-not-exists --no-interaction || true
sudo $DC exec -T php php bin/console doctrine:migrations:migrate --no-interaction

# Si tu utilises AssetMapper (Symfony 6.3+)
if grep -q "symfony/asset-mapper" composer.lock 2>/dev/null; then
  echo -e "${BLUE}🎨 Compilation AssetMapper...${NC}"
  sudo $DC exec -T php php bin/console asset-map:compile --env=prod || true
fi

echo -e "${BLUE}⚡ Cache prod...${NC}"
sudo $DC exec -T php php bin/console cache:clear --env=prod --no-interaction || true
sudo $DC exec -T php php bin/console cache:warmup --env=prod --no-interaction || true

# Permissions (au cas où)
echo -e "${BLUE}🔐 Permissions var/ ...${NC}"
sudo $DC exec -T php bash -lc 'chown -R www-data:www-data var public || true; find var -type d -exec chmod 775 {} \; 2>/dev/null || true'

# Détection port Nginx publié (80 ou 8080)
HOST_IP="$(hostname -I | awk '{print $1}')"
PORT_GUESS="80"
if ! curl -sI --connect-timeout 3 "http://127.0.0.1:${PORT_GUESS}" >/dev/null; then
  PORT_GUESS="8080"
fi

echo ""
echo -e "${GREEN}🎉 DÉPLOIEMENT TERMINÉ !${NC}"
echo "================================"
echo -e "${BLUE}📍 IP du serveur:${NC} ${HOST_IP}"
echo -e "${BLUE}🌐 Site web:${NC} http://${HOST_IP}:${PORT_GUESS}"
echo -e "${BLUE}🔧 Administration:${NC} http://${HOST_IP}:${PORT_GUESS}/admin"
echo -e "${BLUE}📧 Mailpit:${NC} http://${HOST_IP}:8025"
echo ""
echo -e "${YELLOW}💡 Commandes utiles:${NC}"
echo "   sudo $DC ps"
echo "   sudo $DC logs -f nginx php db"
echo "   sudo $DC restart nginx php"
echo "   sudo $DC down"
echo ""
echo -e "${YELLOW}🔒 Conseil:${NC} Ne publie pas le port 3306 de MySQL vers Internet (supprime 'ports:' sur 'db' dans docker-compose)."
