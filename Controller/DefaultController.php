<?php

namespace Ekyna\Bundle\ResourceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('EkynaResourceBundle:Default:index.html.twig');
    }
}
