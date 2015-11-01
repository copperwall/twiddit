<?php

/**
 * View object
 *
 * This allows you to create a view from a template file and add variables to
 * use in the template.
 */
class View {
   protected $template_dir = './templates/';
   protected $template_file;
   protected $vars = [];

   public function __construct($template_file) {
      $this->template_file = $template_file;
   }

   /**
    * Render the template into HTML.
    */
   public function render() {
      if (file_exists($this->template_dir . $this->template_file)) {
         extract($this->vars);
         include $this->template_dir . $this->template_file;
      } else {
         throw new Exception("{$this->template_dir}{$this->template_file} template not found");
      }
   }

   /**
    * Add a variable to be accessible in the template, by the given name.
    *
    * @param $name string - The name to access the variable in the template.
    * @param $value - The variable to add to the template.
    */
   public function addPageVariable($name, $value) {
      $this->vars[$name] = $value;
   }
}
