<?php

class AppController
{ 
    private $request;

    public function __construct()
    {
        $this->request = $_SERVER['REQUEST_METHOD'];
    }

    protected function isGet(): bool
    {
        return $this->request === 'GET';
    }

    protected function isPost(): bool
    {
        return $this->request === 'POST';
    }

    protected function render(string $template = null, array $variables = [])
    {
        $templatePath = 'public/views/'. $template.'.php';
        $output = 'File not found';
                
        if(file_exists($templatePath))
        {
            extract($variables);
            ob_start();
            include $templatePath;
            $output = ob_get_clean();
        }
        echo $output;

        if (isset($messages) && is_array($messages)) 
        {
            echo '<script>';
            foreach ($messages as $message) 
            {
                echo 'alert("' . addslashes($message) . '");';
            }
            echo '</script>';
        }
    }

    
}