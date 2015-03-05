<?php

namespace AppBundle\Phonebook;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\SecurityContext;

class ContactService
{
    private $securityContext;
    private $em;
    private $contactRepository;

    public function __construct(SecurityContext $securityContext, EntityManager $em, EntityRepository $contactRepository)
    {
        $this->securityContext = $securityContext;
        $this->em = $em;
        $this->contactRepository = $contactRepository;
    }

    public function createContact($contact)
    {
        $user = $this->securityContext->getToken()->getUser();
        $contact->setUser($user);
        $this->em->persist($contact);
        $this->em->flush();
    }
}
