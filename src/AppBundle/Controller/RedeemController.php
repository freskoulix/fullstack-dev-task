<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Redeem controller.
 *
 */
class RedeemController extends Controller
{

    /**
 * Lists all Redeem entities.
 *
 */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AppBundle:RedeemCode')->findAll();

        return $this->render('AppBundle:Redeem:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Redeems a given code by a user
     *
     */
    public function redeemMyCodeAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return new Response(json_encode(
                array(
                    'success' => false,
                    'message' => 'Not a Ajax request'
                )
            ));
        }

        $code = $request->request->get('code');
        $userId = $request->request->get('userId');

        if (is_null($code)) {
            return new Response(json_encode(
                array(
                    'success' => false,
                    'message' => 'Invalid code given'
                )
            ));
        }

        if (is_null($userId)) {
            return new Response(json_encode(
                array(
                    'success' => false,
                    'message' => 'Invalid user ID given'
                )
            ));
        }

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AppBundle:RedeemCode')->findOneBy(array('code' => $code));
        if (is_null($entity)) {
            return new Response(json_encode(
                array(
                    'success' => false,
                    'message' => 'Redeem code not found'
                )
            ));
        }

        $used = $entity->getUsed();
        if ($used) {
            return new Response(json_encode(
                array(
                    'success' => false,
                    'message' => 'Redeem code already used'
                )
            ));
        }

        $campaignId = $entity->getCampaignId();
        $campaignEntity = $em->getRepository('AppBundle:Campaign')->find($campaignId);

        $today = new \DateTime('now');
        if ($today < $campaignEntity->getStartDate() || $today > $campaignEntity->getEndDate()) {
            return new Response(json_encode(
                array(
                    'success' => false,
                    'message' => 'Campaign not running'
                )
            ));
        }

        $numOfPrivateProjects = $entity->getProjects();
        $durationDates = $campaignEntity->getDurationDates();
        $expirationDate = $today->add(new \DateInterval('P'.$durationDates.'D'));

        $response = $this->forward('AppBundle:PrivateProjectsPlan:createNewPlan', array(
            'user'  => $userId,
            'numOfPrivateProjects' => $numOfPrivateProjects,
            'expirationDate' => $expirationDate
        ));

        if ($response->getStatusCode() != 200) {
            return new Response(json_encode(
                array(
                    'success' => false,
                    'message' => 'Failed to redeem code'
                )
            ));
        }

        $entity->setUsed(true);

        return new Response(json_encode(
            array(
                'success' => true,
                'message' => 'Code redeemed successfully'
            )
        ));
    }
}
