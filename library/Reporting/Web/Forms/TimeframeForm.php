<?php
// Icinga Reporting | (c) 2019 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\Web\DivDecorator;
use Icinga\Module\Reporting\Web\Flatpickr;
use ipl\Html\Form;
use ipl\Html\FormElement\SubmitElementInterface;
use dipl\Translation\TranslationHelper;

class TimeframeForm extends Form
{
    use Database;
    use DecoratedElement;

    protected $id;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new DivDecorator());

        $this->addElement('text', 'name', [
            'required'  => true,
            'label'     => 'Name'
        ]);

        $flatpickr = new Flatpickr();

        $this->addDecoratedElement($flatpickr, 'text', 'start', [
            'required'            => true,
            'label'               => $this->translate('Start', 'reporting'),
            'placeholder'         => $this->translate('Select a start date or provide a textual date/time description', 'reporting'),
            'data-allow-input'    => true,
            'data-enable-time'    => true,
            'data-enable-seconds' => true,
            'data-default-hour'   => '00'
        ]);

        $this->addDecoratedElement($flatpickr, 'text', 'end', [
            'required'             => true,
            'label'                => $this->translate('End', 'reporting'),
            'placeholder'          => $this->translate('Select an end date or provide a textual date/time description', 'reporting'),
            'data-allow-input'     => true,
            'data-enable-time'     => true,
            'data-enable-seconds'  => true,
            'data-default-hour'    => '23',
            'data-default-minute'  => '59',
            'data-default-seconds' => '59'
        ]);

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? $this->translate('Create Time Frame', 'reporting') : $this->translate('Update Time Frame', 'reporting')
        ]);

        if ($this->id !== null) {
            $this->addElement('submit', 'remove', [
                'label'          => $this->translate('Remove Time Frame', 'reporting'),
                'class'          => 'remove-button',
                'formnovalidate' => true
            ]);

            /** @var SubmitElementInterface $remove */
            $remove = $this->getElement('remove');
            if ($remove->hasBeenPressed()) {
                $this->getDb()->delete('timeframe', ['id = ?' => $this->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->valid = true;

                return;
            }
        }
    }

    public function onSuccess()
    {
        $db = $this->getDb();

        $values = $this->getValues();

        $now = time() * 1000;

        if ($this->id === null) {
            $db->insert('timeframe', [
                'name'  => $values['name'],
                'start' => $values['start'],
                'end'   => $values['end'],
                'ctime' => $now,
                'mtime' => $now
            ]);
        } else {
            $db->update('timeframe', [
                'name'  => $values['name'],
                'start' => $values['start'],
                'end'   => $values['end'],
                'mtime' => $now
            ], ['id = ?' => $this->id]);
        }
    }
}
