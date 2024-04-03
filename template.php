<?php
/**
 * @file
 * Contains theme preprocess functions
 */

/**
 * Format submitted by in articles
 */
function simpleclean_preprocess_node(&$variables) {
  $node = $variables['node'];
  $variables['date'] = format_date($node->created, 'custom', 'd M Y');

  $variables['display_submitted'] = FALSE;
  $variables['submitted'] = '';
  $variables['user_picture'] = '';

  $node_type = node_type_get_type($node->type);
  if ($node_type->settings['node_submitted']) {
    if (!isset($variables['elements']['#display_submitted']) || $variables['elements']['#display_submitted'] == TRUE) {
      $variables['display_submitted'] = TRUE;
      $replacements = array(
        '@username' => strip_tags(theme('username', array('account' => $node))),
        '!datetime' => $variables['date']
      );
      $variables['submitted'] = t('By @username on !datetime', $replacements);
      
      // Add a footer for post
      $account = user_load($variables['node']->uid);
      $variables['simpleclean_postfooter'] = '';
      if (!empty($account->signature)) {  
        $cleansignature = strip_tags($account->signature);

        $postfooter  = "<div class='post-footer'>";
        $postfooter .= $variables['user_picture'];
        $postfooter .= "  <h3>" . check_plain(format_username($account)) . "</h3>";
        $postfooter .= "  <p>" . check_plain($cleansignature) . "</p>";
        $postfooter .= "</div>";
        $variables['simpleclean_postfooter'] = $postfooter;
      } 
    }
  }
  
  // Remove Add new comment from teasers on frontpage
  if ($variables['is_front']) {
    unset($variables['content']['links']['comment']['#links']['comment-add']);
    unset($variables['content']['links']['comment']['#links']['comment_forbidden']);
  }
  
}

/**
 * Format submitted by in comments
 */
function simpleclean_preprocess_comment(&$variables) {
  $comment = $variables['elements']['#comment'];
  $node = $variables['elements']['#node'];
  $variables['created']   = format_date($comment->created, 'custom', 'd M Y');
  $variables['changed']   = format_date($comment->changed, 'custom', 'd M Y');
  $variables['submitted'] = t('By @username on !datetime at about @time.', array('@username' => strip_tags(theme('username', array('account' => $comment))), '!datetime' => $variables['created'], '@time' => format_date($comment->created, 'custom', 'H:i')));
}

/**
 * Change button to Post instead of Save
 */
function simpleclean_form_comment_form_alter(&$form, &$form_state, &$form_id) {
 $form['actions']['submit']['#value'] = t('Post');
 $form['comment_body']['#after_build'][] = 'configure_comment_form'; 
}

/**
 * After build handler: Remove access to text format.
 */
function configure_comment_form(&$form) {
  $form['und'][0]['format']['#access'] = FALSE;
  return $form;
}
