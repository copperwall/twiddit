<?php

require_once('Reddit.php');
require_once('View.php');

$users = ['kn0thing', 'zolokar', 'xiongchiamiov'];
$comments = Reddit::getComments($users);
$template = new View('base.phtml');

$template->addPageVariable('comments', $comments);
$template->render();
