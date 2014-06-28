<?php

namespace CakeResque\Lib;

class MyView extends \Slim\View
{

    private $headerTemplate = 'header.php';
    private $footerTemplate = 'footer.php';

    public function render( $template )
    {
       $this->setTemplate($template);
       extract($this->data);
       ob_start();
       require ROOT . DS . 'Templates' . DIRECTORY_SEPARATOR . $this->headerTemplate;
       echo \Michelf\MarkdownExtra::defaultTransform(file_get_contents($this->templatePath));
       require ROOT . DS . 'Templates' . DIRECTORY_SEPARATOR . $this->footerTemplate;
       return ob_get_clean();
    }
}