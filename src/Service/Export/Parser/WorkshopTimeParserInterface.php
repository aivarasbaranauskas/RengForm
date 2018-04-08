<?php

namespace App\Service\Export\Parser;

use App\Entity\Registration;
use App\Entity\WorkshopTime;

/**
 * Class WorkshopTimeParser
 */
class WorkshopTimeParserInterface implements ParserInterface
{
    /**
     * @param $object
     * @return array
     * @throws \Exception
     */
    public static function parse($object): array
    {
        if (!($object instanceof WorkshopTime)) {
            throw new \Exception(sprintf('WorkshopTime expected, got %s', get_class($object)));
        }

        $data = [
            [
                'Workshop',
                $object->getWorkshop()->getTitle(),
                $object->getStartTime()->format('Y-m-d H:i')
            ],
        ];

        if (is_iterable($object->getRegistrations()) && count($object->getRegistrations()) > 0) {
            $fieldList = [];

            foreach ($object->getWorkshop()->getFormConfig()->getConfigParsed() as $field) {
                if ($field['type'] != 'paragraph') {
                    $fieldList[$field['name']] = $field['label'];
                }
            }

            $data[] = array_merge(['Registration Date'], array_values($fieldList));

            /** @var Registration $registration */
            foreach ($object->getRegistrations() as $registration) {
                $row = [
                    $registration->getCreated()->format('Y-m-d H:i'),
                ];

                $rawData = $registration->getData();
                foreach (array_keys($fieldList) as $field) {
                    if (is_array($rawData[$field])) {
                        $row[] = join(', ', $rawData[$field]);
                    } else {
                        $row[] = $rawData[$field];
                    }
                }

                $data[] = $row;
            }
        } else {
            $data[] = ['No registrations in workshop'];
        }

        return $data;
    }
}
