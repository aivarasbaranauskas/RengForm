<?php

namespace App\Service\Export\Parser;

use App\Entity\EventTime;
use App\Entity\Registration;

/**
 * Class EventTimeParser
 */
class EventTimeParser implements ParserInterface
{
    /**
     * @param $object
     * @return array
     * @throws \Exception
     */
    public static function parse($object): array
    {
        if (!($object instanceof EventTime)) {
            throw new \Exception(sprintf('EventTime expected, got %s', get_class($object)));
        }

        $data = [
            [
                'Event',
                $object->getEvent()->getTitle(),
                $object->getStartTime()->format('Y-m-d H:i'),
            ],
        ];

        if (is_iterable($object->getRegistrations()) && count($object->getRegistrations()) > 0) {
            if ($object->getEvent()->getFormConfig() !== null) {
                $fieldList = [];

                foreach ($object->getEvent()->getFormConfig()->getConfigParsed() as $field) {
                    if ($field['type'] != 'paragraph') {
                        $fieldList[$field['name']] = $field['label'];
                    }
                }

                $data[] = [];
                $data[] = ['Single registrations'];
                $data = self::parseRegistrations($object, $fieldList, $data);
            }

            if ($object->getEvent()->getGroupFormConfig() !== null) {
                $fieldList = [];

                foreach ($object->getEvent()->getGroupFormConfig()->getConfigParsed() as $field) {
                    if ($field['type'] != 'paragraph') {
                        $fieldList[$field['name']] = $field['label'];
                    }
                }

                $data[] = [];
                $data[] = ['Group registrations'];
                $data = self::parseRegistrations($object, $fieldList, $data, true);
            }
        } else {
            $data[] = ['No registrations in event'];
        }

        return $data;
    }

    /**
     * @param EventTime $object
     * @param array     $fieldList
     * @param array     $data
     * @param bool      $group
     * @return array
     */
    private static function parseRegistrations(
        EventTime $object,
        array $fieldList,
        array $data,
        bool $group = false
    ): array {
        $data[] = array_merge(['Registration Date'], array_values($fieldList));

        /** @var Registration $registration */
        foreach ($object->getRegistrations() as $registration) {
            if ($registration->isGroupRegistration() && !$group) {
                continue;
            }

            $row = [
                $registration->getCreated()->format('Y-m-d H:i'),
            ];

            $rawData = $registration->getData();
            foreach (array_keys($fieldList) as $field) {
                if (!isset($rawData[$field])) {
                    //TODO: change this so missing fields would output something
                    //and fields that are no longer in config would be exported
                    continue;
                }

                if (is_array($rawData[$field])) {
                    $row[] = join(', ', $rawData[$field]);
                } else {
                    $row[] = $rawData[$field];
                }
            }

            $data[] = $row;
        }

        return $data;
    }
}
