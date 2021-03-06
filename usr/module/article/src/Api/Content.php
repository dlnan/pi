<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Application\Api\AbstractContent;

/**
 * Public API for content fetch
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Content extends AbstractContent
{
    /**
     * {@inheritDoc}
     */
    protected $module = 'article';

    /**
     * {@inheritDoc}
     */
    protected $table = 'article';

    /**
     * {@inheritDoc}
     */
    protected $meta
        = [
            'id'           => 'id',
            'subject'      => 'title',
            'summary'      => 'content',
            'time_publish' => 'time',
            'uid'          => 'uid',
        ];

    /**
     * {@inheritDoc}
     */
    public function ____getList(
        array $variables,
        array $conditions,
        $limit = 0,
        $offset = 0,
        $order = []
    ) {
        $result = [];
        $model  = Pi::model('article', $this->module);
        $select = $model->select();
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if ($order) {
            $select->order($order);
        }
        $select->columns($this->canonizeVariables($variables));
        $select->where($this->canonizeConditions($conditions));
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $item          = $row->toArray();
            $item['title'] = $item['subject'];
            unset($item['subject']);
            $item['url'] = $this->buildUrl($item);
            $result[]    = $item;
        }

        return $result;
    }

    /**
     * Create link
     *
     * @param array $item
     *
     * @return string
     */
    protected function buildUrl(array $item)
    {
        $link = Pi::service('url')->assemble(
            'article',
            ['id' => $item['id']]
        );

        return $link;
    }
}
