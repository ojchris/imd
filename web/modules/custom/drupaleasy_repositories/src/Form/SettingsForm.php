<?php

namespace Drupal\drupaleasy_repositories\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure DrupalEasy Repositories settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drupaleasy_repositories_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['drupaleasy_repositories.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state):array {
    /* $form['example'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Example'),
    '#default_value' => $this->config('drupaleasy_repositories.settings')->
    get('example'),
    ];
    return parent::buildForm($form, $form_state); */

    $repositories_config = $this->config('drupaleasy_repositories.settings');

    $form['repositories'] = [
      '#type' => 'checkboxes',
      '#options' => [
        'yml_remote' => $this->t('Yml remote'),
        'github' => $this->t('Github'),
        'bitbucket' => $this->t('Bitbucket'),
      ],
      '#title' => $this->t('Repositories'),
      '#default_value' => $repositories_config->get('repositories') ?: [],
    ];

    return parent::buildForm($form, $form_state);

  }

  /**
   * {@inheritdoc}
   */
  /* public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('example') != 'example') {
      $form_state->setErrorByName('example', $this->t('The value is not correct.'));
    }
    parent::validateForm($form, $form_state);
  }*/

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('drupaleasy_repositories.settings')
      ->set('repositories', $form_state->getValue('repositories'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
