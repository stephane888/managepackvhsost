<?php

/**
 *
 * @param [] $vars
 */
function template_preprocess_managepackvhsost_blocks(&$vars) {
  //
  $vars['image'] = $vars['element']['#image'];
  $vars['name_site'] = $vars['element']['#name_site'];
  $vars['date'] = $vars['element']['#date'];
  $vars['domaines'] = $vars['element']['#domaines'];
  $vars['change_domain'] = $vars['element']['#change_domain'];
  $vars['dissociate_domain'] = $vars['element']['#dissociate_domain'];
  $vars['souscription'] = $vars['element']['#souscription'];
}