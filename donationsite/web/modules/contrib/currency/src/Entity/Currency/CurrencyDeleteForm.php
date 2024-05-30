<?php

namespace Drupal\currency\Entity\Currency;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the currency deletion form.
 */
class CurrencyDeleteForm extends EntityConfirmFormBase {

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   */
  public function __construct(TranslationInterface $string_translation) {
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $currency = $this->getEntity();

    return $this->t('Do you really want to delete %label?', array(
      '%label' => $currency->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.currency.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $currency = $this->getEntity();
    $currency->delete();
    $this->messenger()->addMessage($this->t('The %label has been deleted.', array(
      '%label' => $currency->label(),
    )));
    $form_state->setRedirectUrl($this->getCancelUrl());
  }
}
