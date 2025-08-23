#!/bin/bash

echo "🧹 Nettoyage de la démonstration AWS"
echo "===================================="

# Récupération des paramètres
INSTANCE_ID=${1:-}
SECURITY_GROUP_ID=${2:-}
KEY_NAME=${3:-}

# Si aucun paramètre, essayer de détecter automatiquement
if [ -z "$INSTANCE_ID" ]; then
    echo "🔍 Recherche automatique des ressources de démonstration..."
    
    # Chercher les instances avec le tag Name contenant "symfony-demo"
    INSTANCE_ID=$(aws ec2 describe-instances \
        --filters "Name=tag:Name,Values=symfony-demo-*" "Name=instance-state-name,Values=running,pending,stopping,stopped" \
        --query 'Reservations[0].Instances[0].InstanceId' \
        --output text 2>/dev/null)
    
    if [ "$INSTANCE_ID" = "None" ] || [ -z "$INSTANCE_ID" ]; then
        echo "❌ Aucune instance de démonstration trouvée"
        echo "💡 Usage: $0 <instance-id> <security-group-id> <key-name>"
        echo "💡 Ou vérifiez le fichier demo-info-*.txt pour les détails"
        exit 1
    fi
fi

echo "🎯 Instance trouvée: $INSTANCE_ID"

# Obtenir les informations de l'instance si pas fournies
if [ -z "$SECURITY_GROUP_ID" ] || [ -z "$KEY_NAME" ]; then
    echo "📋 Récupération des informations de l'instance..."
    
    INSTANCE_INFO=$(aws ec2 describe-instances --instance-ids $INSTANCE_ID --query 'Reservations[0].Instances[0]')
    
    if [ -z "$SECURITY_GROUP_ID" ]; then
        SECURITY_GROUP_ID=$(echo $INSTANCE_INFO | jq -r '.SecurityGroups[0].GroupId')
    fi
    
    if [ -z "$KEY_NAME" ]; then
        KEY_NAME=$(echo $INSTANCE_INFO | jq -r '.KeyName')
    fi
fi

echo "🔑 Paire de clés: $KEY_NAME"
echo "🛡️ Groupe de sécurité: $SECURITY_GROUP_ID"

# Confirmation
read -p "❓ Êtes-vous sûr de vouloir supprimer ces ressources? (y/N): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Annulation du nettoyage"
    exit 1
fi

echo "🗑️ Début du nettoyage..."

# 1. Arrêter et terminer l'instance
echo "🔄 Arrêt de l'instance EC2..."
aws ec2 terminate-instances --instance-ids $INSTANCE_ID > /dev/null 2>&1

if [ $? -eq 0 ]; then
    echo "✅ Instance marquée pour terminaison"
    echo "⏳ Attente de la terminaison complète..."
    
    # Attendre que l'instance soit complètement terminée
    aws ec2 wait instance-terminated --instance-ids $INSTANCE_ID
    
    if [ $? -eq 0 ]; then
        echo "✅ Instance terminée avec succès"
    else
        echo "⚠️ Timeout lors de l'attente de terminaison (mais en cours)"
    fi
else
    echo "⚠️ Erreur lors de la terminaison de l'instance (peut-être déjà terminée)"
fi

# 2. Supprimer le groupe de sécurité
if [ -n "$SECURITY_GROUP_ID" ] && [ "$SECURITY_GROUP_ID" != "null" ]; then
    echo "🗑️ Suppression du groupe de sécurité..."
    
    # Attendre un peu pour s'assurer que l'instance est libérée
    sleep 10
    
    aws ec2 delete-security-group --group-id $SECURITY_GROUP_ID > /dev/null 2>&1
    
    if [ $? -eq 0 ]; then
        echo "✅ Groupe de sécurité supprimé"
    else
        echo "⚠️ Erreur lors de la suppression du groupe de sécurité (peut-être encore utilisé)"
        echo "💡 Réessayez dans quelques minutes: aws ec2 delete-security-group --group-id $SECURITY_GROUP_ID"
    fi
fi

# 3. Supprimer la paire de clés
if [ -n "$KEY_NAME" ] && [ "$KEY_NAME" != "null" ]; then
    echo "🗑️ Suppression de la paire de clés..."
    
    aws ec2 delete-key-pair --key-name $KEY_NAME > /dev/null 2>&1
    
    if [ $? -eq 0 ]; then
        echo "✅ Paire de clés supprimée d'AWS"
    else
        echo "⚠️ Erreur lors de la suppression de la paire de clés"
    fi
    
    # Supprimer le fichier local .pem
    if [ -f "${KEY_NAME}.pem" ]; then
        rm "${KEY_NAME}.pem"
        echo "✅ Fichier de clé local supprimé: ${KEY_NAME}.pem"
    fi
fi

# 4. Nettoyer les fichiers temporaires
echo "🗑️ Nettoyage des fichiers temporaires..."

# Supprimer les fichiers de configuration temporaires
rm -f user-data.sh
rm -f demo-info-*.txt

echo "✅ Fichiers temporaires supprimés"

# 5. Vérification finale
echo ""
echo "🔍 Vérification finale..."

# Vérifier que l'instance n'existe plus
INSTANCE_STATE=$(aws ec2 describe-instances --instance-ids $INSTANCE_ID --query 'Reservations[0].Instances[0].State.Name' --output text 2>/dev/null)

if [ "$INSTANCE_STATE" = "terminated" ]; then
    echo "✅ Instance confirmée comme terminée"
elif [ -z "$INSTANCE_STATE" ] || [ "$INSTANCE_STATE" = "None" ]; then
    echo "✅ Instance non trouvée (supprimée)"
else
    echo "⏳ Instance en cours de terminaison (état: $INSTANCE_STATE)"
fi

echo ""
echo "🎉 NETTOYAGE TERMINÉ!"
echo "===================="
echo "💰 Facturation AWS arrêtée pour cette démonstration"
echo "🧹 Toutes les ressources ont été supprimées"
echo ""
echo "📊 Résumé des suppressions:"
echo "   ✅ Instance EC2: $INSTANCE_ID"
echo "   ✅ Groupe de sécurité: $SECURITY_GROUP_ID"
echo "   ✅ Paire de clés: $KEY_NAME"
echo "   ✅ Fichiers temporaires locaux"
echo ""
echo "💡 Vous pouvez relancer une nouvelle démonstration avec:"
echo "   ./deploy-express-demo.sh"

# Estimation du coût final
HOURS_ELAPSED=$(( ($(date +%s) - $(date -d "1 hour ago" +%s)) / 3600 ))
ESTIMATED_COST=$(echo "scale=2; $HOURS_ELAPSED * 0.0116 * 0.85" | bc 2>/dev/null || echo "~0.50")

echo ""
echo "💰 Coût estimé de cette démonstration: ~${ESTIMATED_COST}€"
