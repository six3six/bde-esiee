<?php

namespace Application\StudentBundle\Controller;

use Ferus\FairPayApi\Exception\ApiErrorException;
use Ferus\FairPayApi\FairPay;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use FOS\RestBundle\Controller\FOSRestController;
use Application\StudentBundle\Entity\Student;

class StudentController extends FOSRestController
{
    public function searchAction($query)
    {
        try{
            $fairpay = new FairPay();
            if ((boolean) $this->container->getParameter('use_proxy')) {
	            $fairpay->setCurlParam(CURLOPT_HTTPPROXYTUNNEL, true);
	            $fairpay->setCurlParam(CURLOPT_PROXY, "proxy.esiee.fr:3128");
            }
            $student = $fairpay->getStudent($query);
        }
        catch(ApiErrorException $e){
            return new Response(json_encode($e->returned_value), $e->returned_value->code);
        }
        return new Response(json_encode($student), 200);
    }

    /**
     * @Template
     */
    public function amIContributorAction() 
    {
        $form = $this->createFormBuilder()
            ->add('name', 'student')
            ->getForm();

        return array(
            'form' => $form->createView(),
        );
    }

    public function clubsByStudentAction(Student $student)
    {
        $em = $this->getDoctrine()->getManager();

        $clubs = $em->getRepository('ApplicationStudentBundle:StudentHasClub')->findClubsbyStudent($student);
        $clubsSorted = array();

        foreach ($clubs as $club) {
            if ($club[1])
                $clubsSorted[] = intval($club[1]);
            else
                $clubsSorted[] = intval($club[2]);
        }

        $view = $this->view($clubsSorted, 200);

        return $this->handleView($view);
    }
}
