<?php

namespace MeloLab\BioGestion\FileUploadBundle\Classes;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Created by PhpStorm.
 * User: andreas
 * Date: 9/9/2016
 * Time: 17:23
 */
class UploadUtils {

    static public function copyFormErrors($oldForm, $newForm) {
        foreach ($oldForm->getErrors(true, true) as $error) {
            $path = preg_replace('/^data\./', '', $error->getCause()->getPropertyPath());
            $pieces = explode('.', $path);
            $field = $newForm;
            foreach ($pieces as $piece) {
                $pieces2 = preg_split('/[\[\]]/', $piece);
                $field = $field->get($pieces2[0]);
                if (count($pieces2) > 1) {
                    $field = $field[$pieces2[1]];
                }
            }
            $field->addError($error);
        }
    }

    static public function addFormErrors(FormInterface $form, ConstraintViolationListInterface $violationList) {
        foreach ($violationList as $violation) {
            $path = preg_replace('/^data\./', '', $violation->getPropertyPath());
            $pieces = explode('.', $path);
            $field = $form;
            foreach ($pieces as $piece) {
                $pieces2 = preg_split('/[\[\]]/', $piece);
                $field = $field->get($pieces2[0]);
                if (count($pieces2) > 1) {
                    $field = $field[$pieces2[1]];
                }
            }
            $field->addError(new FormError($violation->getMessage()));
        }
    }

}