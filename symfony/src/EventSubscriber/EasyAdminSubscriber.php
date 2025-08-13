<?php

namespace App\EventSubscriber;

use App\Entity\Category;
use App\Entity\Joke;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeEntityPersistedEvent::class => ['setEntityDefaults'],
            BeforeEntityUpdatedEvent::class => ['updateEntityDefaults'],
        ];
    }

    public function setEntityDefaults(BeforeEntityPersistedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Category) {
            $this->setCategoryDefaults($entity);
        }

        if ($entity instanceof Joke) {
            $this->setJokeDefaults($entity);
        }
    }

    public function updateEntityDefaults(BeforeEntityUpdatedEvent $event): void
    {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Category) {
            $this->updateCategory($entity);
        }

        if ($entity instanceof Joke) {
            $this->updateJoke($entity);
        }
    }

    private function setCategoryDefaults(Category $category): void
    {
        if (!$category->getSlug() && $category->getName()) {
            $slug = $this->slugger->slug($category->getName())->lower();
            $category->setSlug($slug);
        }

        if (!$category->getCreatedAt()) {
            $category->setCreatedAt(new \DateTimeImmutable());
        }

        $category->setUpdatedAt(new \DateTimeImmutable());
    }

    private function updateCategory(Category $category): void
    {
        if ($category->getName()) {
            $slug = $this->slugger->slug($category->getName())->lower();
            $category->setSlug($slug);
        }

        $category->setUpdatedAt(new \DateTimeImmutable());
    }

    private function setJokeDefaults(Joke $joke): void
    {
        if (!$joke->getCreatedAt()) {
            $joke->setCreatedAt(new \DateTimeImmutable());
        }

        $joke->setUpdatedAt(new \DateTimeImmutable());
    }

    private function updateJoke(Joke $joke): void
    {
        $joke->setUpdatedAt(new \DateTimeImmutable());
    }
}
