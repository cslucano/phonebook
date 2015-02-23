<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BasicSearch;
use AppBundle\Entity\Contact;
use AppBundle\Form\BasicSearchType;
use AppBundle\Form\ContactType;
use Knp\Component\Pager\Paginator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('default/index.html.twig');
    }

    /**
     * @Route("/dashboard", name="dashboard")
     * @Security("is_fully_authenticated()")
     * @param Request $request
     *
     * @return Response
     */
    public function dashboardAction(Request $request)
    {
        $basicSearch = new BasicSearch();
        $form = $this->createBasicSearchForm($basicSearch);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $q = $form->get('queryString')->getData();
        } else {
            $q = '';
        }

        $em = $this->getDoctrine()->getManager();

        /** @var Paginator $paginator */
        $paginator = $this->get('knp_paginator');
        $page = $request->query->get('page', 1);

        $user = $this->getUser();

        $contacts = $em->getRepository('AppBundle:Contact')->contactSearch($user, $paginator, $q, $page, 2);

        return $this->render(
            'default/dashboard.html.twig',
            [
                'contacts' => $contacts,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Creates a form for a Basic Search.
     *
     * @param BasicSearch $basicSearch The entity
     *
     * @return Form The form
     */
    private function createBasicSearchForm(BasicSearch $basicSearch)
    {
        $form = $this->createForm(new BasicSearchType(), $basicSearch, array(
            'action' => $this->generateUrl('dashboard'),
            'method' => 'GET',
        ));

        $form->add('submit', 'submit', array('label' => 'Search'));

        return $form;
    }

    /**
     * Creates a new Contact entity.
     * @Security("is_fully_authenticated()")
     *
     * @Route("/contact", name="contact_create")
     * @Method("POST")
     */
    public function createAction(Request $request)
    {
        $contact = new Contact();
        $form = $this->createCreateForm($contact);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $contact->setUser($this->getUser());
            $em->persist($contact);
            $em->flush();

            return $this->redirect($this->generateUrl('dashboard'));
        }

        return $this->render(
            'default/contact_new.html.twig',
            [
                'contact' => $contact,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * Creates a form to create a Contact entity.
     *
     * @param Contact $entity The entity
     *
     * @return Form The form
     */
    private function createCreateForm(Contact $entity)
    {
        $form = $this->createForm(new ContactType(), $entity, array(
            'action' => $this->generateUrl('contact_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Contact entity.
     * @Security("is_fully_authenticated()")
     *
     * @Route("/contact/new", name="contact_new")
     * @Method("GET")
     */
    public function newAction()
    {
        $contact = new Contact();
        $form   = $this->createCreateForm($contact);

        return $this->render(
            'default/contact_new.html.twig',
            [
                'contact' => $contact,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * Displays a form to edit an existing Contact entity.
     * @Security("is_fully_authenticated()")
     *
     * @Route("/contact/{id}/edit", name="contact_edit")
     * @Method("GET")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $contact = $em->getRepository('AppBundle:Contact')->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Unable to find Contact entity.');
        }

        if ($contact->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access Denied Exception');
        }

        $form = $this->createEditForm($contact);

        return $this->render(
            'default/contact_edit.html.twig',
            [
                'contact'      => $contact,
                'form'   => $form->createView(),
            ]
        );
    }

    /**
     * Creates a form to edit a Contact entity.
     *
     * @param Contact $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Contact $entity)
    {
        $form = $this->createForm(new ContactType(), $entity, array(
            'action' => $this->generateUrl('contact_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Contact entity.
     * @Security("is_fully_authenticated()")
     *
     * @Route("/contact/{id}", name="contact_update")
     * @Method("PUT")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $contact = $em->getRepository('AppBundle:Contact')->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Unable to find Contact entity.');
        }

        if ($contact->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access Denied Exception');
        }

        $editForm = $this->createEditForm($contact);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('dashboard'));
        }

        return $this->render(
            'default/contact_edit.html.twig',
            [
                'contact'      => $contact,
                'edit_form'   => $editForm->createView(),
            ]
        );
    }

    /**
     * Deletes a Contact entity.
     * @Security("is_fully_authenticated()")
     *
     * @Route("/contact/{id}/delete", name="contact_delete")
     * @Method("GET")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $contact = $em->getRepository('AppBundle:Contact')->find($id);

        if (!$contact) {
            throw $this->createNotFoundException('Unable to find Contact entity.');
        }

        if ($contact->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Access Denied Exception');
        }

        $em->remove($contact);
        $em->flush();

        return $this->redirect($this->generateUrl('dashboard'));
    }
}
