<?php

namespace App\Tests\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DashboardControllerTest extends WebTestCase
{
    public function testDashboardWithoutJokes(): void
    {
        $client = static::createClient();

        // Simuler un utilisateur admin connecté
        // Note: Vous devrez adapter cela selon votre système d'authentification

        $crawler = $client->request('GET', '/admin');

        // Vérifier que la page se charge sans erreur de division par zéro
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Vérifier que "0% du total" est affiché quand il n'y a pas de blagues
        $this->assertSelectorTextContains('.card-footer small', '0% du total');
    }

    public function testDashboardWithJokes(): void
    {
        $client = static::createClient();

        // Note: Ici vous pourriez créer des blagues de test
        // et vérifier que le pourcentage est correctement calculé

        $crawler = $client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // Vérifier que le pourcentage est affiché
        $this->assertSelectorExists('.card-footer small');
    }
}
