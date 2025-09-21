<?php
require '../Components/generalComponents.php';
require '../Components/formComponents.php';

$general_components = new generalComponents();
$form_components   = new formComponents();
$type='signUp';

$general_components->formArea($form_components);
?>