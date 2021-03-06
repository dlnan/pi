<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 * @package         Form
 */

namespace Pi\Form\Element;

use DateTimeZone;
use Pi;
use Laminas\Form\Element\Select;

/**
 * Navigation select element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Timezone extends Select
{
    /** @var array Timezones */
    protected static $timezones = [];

    /**
     * Get options of value select
     *
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            if (!static::$timezones) {
                Pi::service('i18n')->load('timezone');
                $groups = [];
                foreach (DateTimeZone::listIdentifiers() as $timezone) {
                    if ($pos = strpos($timezone, '/')) {
                        $group = substr($timezone, 0, $pos);
                    } else {
                        $group = $timezone;
                    }
                    $groups[$group]['options'][$timezone] = __($timezone);
                }
                array_walk(
                    $groups,
                    function (&$data, $group) {
                        $data['label'] = __($group);
                        $data['value'] = $group;
                    }
                );

                static::$timezones = $groups;
            }
            $this->valueOptions = static::$timezones;
        }

        return $this->valueOptions;
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        if (!$this->value) {
            $this->value = date_default_timezone_get();
        }

        return parent::getValue();
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        if (null === $this->label) {
            $this->label = __('Timezone');
        }

        return parent::getLabel();
    }
}
