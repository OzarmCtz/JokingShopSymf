#!/usr/bin/env bash
set -Eeuo pipefail

echo "🚀 Déploiement Jo-King sur Debian"
echo "==============================================="

# Nettoyer d'abord tout dépôt Docker cassé
sudo rm -f /etc/apt/sources.list.d/docker.list /etc/apt/keyrings/docker.gpg || true

# Pré-requis de base
sudo apt update -y
sudo apt install -y ca-certificates curl gnupg lsb-release git unzip

# Installer Docker (clé + repo bookworm)
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/debian/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian bookworm stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list >/dev/null
sudo apt update -y
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo systemctl enable --now docker
sudo usermod -aG docker ${SUDO_USER:-$USER}
newgrp docker
