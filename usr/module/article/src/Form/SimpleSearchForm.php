<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Pi\Form\Form as BaseForm;

/**
 * Simple search form class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class SimpleSearchForm extends BaseForm
{
    public function init()
    {
        $this->add([
            'name'       => 'keyword',
            'options'    => [
                'label' => '',
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'value' => __('Search'),
            ],
            'type'       => 'submit',
        ]);
    }
}
