<?php
  require "../Components/formComponents.php";
  require '../Components/generalComponents.php';

  $general_components = new generalComponents();
  $form_components   = new formComponents();

  $general_components->formArea($form_components);
?>