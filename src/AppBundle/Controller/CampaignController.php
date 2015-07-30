<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Entity\Campaign;
use AppBundle\Entity\RedeemCode;
use AppBundle\Form\CampaignType;

/**
 * Campaign controller.
 *
 */
class CampaignController extends Controller
{

    /**
     * Lists all Project entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:Campaign')->findAll();

        return $this->render('AppBundle:Campaign:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Campaign entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity = new Campaign();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            // Generate redeem codes
            $codes = $entity->getCodes();
            for ($i = 0; $i <= $codes; $i++) {
                $entityRedeemCode = new RedeemCode();

                $entityRedeemCode->setCode(uniqid());
                $entityRedeemCode->setCampaignId($entity->getId());
                $entityRedeemCode->setProjects($entity->getNumber());
                $entityRedeemCode->setUsed(false);

                $em->persist($entityRedeemCode);
            }
            $em->flush();

            return $this->redirect($this->generateUrl('campaign_show', array('id' => $entity->getId())));
        }

        return $this->render('AppBundle:Campaign:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Campaign entity.
     *
     * @param Campaign $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Campaign $entity)
    {
        $form = $this->createFormBuilder($entity)
            ->setAction($this->generateUrl('campaign_create'))
            ->setMethod('POST')
            ->add('codes', 'text')
            ->add('number', 'text')
            ->add('durationDates', 'text')
            ->add('startDate', 'datetime')
            ->add('endDate', 'datetime')
            ->add('submit', 'submit', array('label' => 'Create Campaign'))
            ->getForm();

        return $form;
    }

    /**
     * Displays a form to create a new Campaign entity.
     *
     */
    public function newAction()
    {
        $entity = new Campaign();
        $form   = $this->createCreateForm($entity);

        return $this->render('AppBundle:Campaign:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Campaign entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Campaign')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Campaigns entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Campaign:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Campaign entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Campaign')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Campaign entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('AppBundle:Campaign:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Campaign entity.
     *
     * @param Campaign $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Campaign $entity)
    {
        $form = $this->createForm(new CampaignType(), $entity, array(
            'action' => $this->generateUrl('project_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Campaign entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:Campaign')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Campaign entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('campaign_edit', array('id' => $id)));
        }

        return $this->render('AppBundle:Campaign:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Campaign entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AppBundle:Campaign')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Campaign entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('project'));
    }

    /**
     * Creates a form to delete a Campaign entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('campaign_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
            ;
    }
}
