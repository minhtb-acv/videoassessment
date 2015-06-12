<?php
/* MinhTB VERSION 2 */

namespace videoassess\form;

use \videoassess\va;

defined('MOODLE_INTERNAL') || die();

class assign_class extends \moodleform {

    CONST SORT_ID = 1;
    CONST SORT_NAME = 2;
    CONST SORT_MANUALLY = 3;

    CONST ORDER_ASC = 1;
    CONST ORDER_DESC = 2;

    public function definition() {
        global $DB, $OUTPUT;

        $mform = $this->_form;
        /* @var $va \videoassess\va */
        $va = $this->_customdata->va;

        $attrs = $mform->getAttributes();
        $attrs['class'] .= ' sort-form';
        $mform->setAttributes($attrs);
        $mform->addElement('hidden', 'id', $va->cm->id);
        $mform->setType('id', PARAM_INT);

        $sort_options = array(
            self::SORT_ID => get_string('sortid', 'videoassessment'),
            self::SORT_NAME => get_string('sortname', 'videoassessment'),
            self::SORT_MANUALLY => get_string('sortmanually', 'videoassessment')
        );
        $mform->addElement('select', 'sortby', get_string('sortby', 'videoassessment'), $sort_options, array('id' => 'sortby', 'data-load' => 0));
        $mform->setType('sortby', PARAM_INT);
        $mform->setDefault('sortby', $this->_customdata->sort);

        $order_options = array(
            self::ORDER_ASC => get_string('orderasc', 'videoassessment'),
            self::ORDER_DESC => get_string('orderdesc', 'videoassessment')
        );

        $attributes = array();
        if ($this->_customdata->sort == self::SORT_MANUALLY) {
            $attributes['class'] = 'hidden';
        }

        $mform->addElement('select', 'order', get_string('order', 'videoassessment'), $order_options, $attributes);
        $mform->setType('order', PARAM_INT);
        $mform->setDefault('order', $this->_customdata->order);

        $this->add_action_buttons(false, va::str('save'));
    }

    /**
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        return $errors;
    }
}