<?php

namespace App\Service\Form;

use App\Entity\EventTime;
use App\Entity\WorkshopTime;
use Psr\Log\LoggerInterface;

/**
 * Class FormValidator
 */
class FormValidator
{
    const VALIDATORS = [
        Validator\TextField::TYPE          => Validator\TextField::class,
        Validator\DateField::TYPE          => Validator\DateField::class,
        Validator\SelectField::TYPE        => Validator\SelectField::class,
        Validator\NumberField::TYPE        => Validator\NumberField::class,
        Validator\TextAreaField::TYPE      => Validator\TextAreaField::class,
        Validator\RadioGroupField::TYPE    => Validator\RadioGroupField::class,
        Validator\CheckboxGroupField::TYPE => Validator\CheckboxGroupField::class,
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FormValidator constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param EventTime|WorkshopTime $time
     * @param array                  $formData
     * @return bool
     */
    public function validate($time, array $formData): bool
    {
        if (empty($formData)) {
            return false;
        }
        $errors = [];

        try {
            $form = $this->getFormConfig($time);

            foreach ($form->getFields() as $field) {
                $validator = $this->getValidator($field->getType());
                $errors = array_merge($errors, $validator->validate($field, $formData));
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $errors[] = 'Unknown validation error occurred, please try again';
        }

        return empty($errors);
    }

    /**
     * @param string $type
     * @return Validator\ValidatorInterface
     * @throws \Exception
     */
    private function getValidator(string $type): Validator\ValidatorInterface
    {
        if (!isset(self::VALIDATORS[$type])) {
            throw new \Exception(sprintf('Validator for type %s not found', $type));
        }

        $validator = self::VALIDATORS[$type];
        $validator = new $validator();

        return $validator;
    }

    /**
     * @param EventTime|WorkshopTime $time
     * @return Form
     * @throws \Exception
     */
    private function getFormConfig($time): Form
    {
        switch (get_class($time)) {
            case EventTime::class:
                $formConfig = $time->getEvent()->getFormConfig();
                break;
            case WorkshopTime::class:
                $formConfig = $time->getWorkshop()->getFormConfig();
                break;
            default:
                throw new \Exception(sprintf('Unsupported class %s', get_class($time)));
                break;
        }

        $form = new Form($formConfig->getConfigParsed());

        return $form;
    }
}