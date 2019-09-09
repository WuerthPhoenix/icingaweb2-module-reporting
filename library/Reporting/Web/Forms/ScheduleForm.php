<?php
// Icinga Reporting | (c) 2018 Icinga GmbH | GPLv2

namespace Icinga\Module\Reporting\Web\Forms;

use Icinga\Authentication\Auth;
use Icinga\Module\Reporting\Database;
use Icinga\Module\Reporting\ProvidedActions;
use Icinga\Module\Reporting\Report;
use Icinga\Module\Reporting\Web\DivDecorator;
use Icinga\Module\Reporting\Web\Flatpickr;
use ipl\Html\Form;
use ipl\Html\FormElement\SubmitElementInterface;
use ipl\Html\FormElement\TextareaElement;
use Icinga\Util\Translator;

class ScheduleForm extends Form
{
    use Database;
    use DecoratedElement;
    use ProvidedActions;

    /** @var Report */
    protected $report;

    protected $id;

    public function setReport(Report $report)
    {
        $this->report = $report;

        $schedule = $report->getSchedule();

        if ($schedule !== null) {
            $this->setId($schedule->getId());

            $values = [
                'start'     => $schedule->getStart()->format('Y-m-d H:i'),
                'frequency' => $schedule->getFrequency(),
                'action'    => $schedule->getAction()
            ] + $schedule->getConfig();

            $this->populate($values);
        }

        return $this;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    protected function assemble()
    {
        $this->setDefaultElementDecorator(new DivDecorator());

        $frequency = [
            'minutely' => Translator::translate('Per Minute', 'reporting'),
            'hourly'   => Translator::translate('Hourly', 'reporting'),
            'daily'    => Translator::translate('Daily', 'reporting'),
            'weekly'   => Translator::translate('Weekly', 'reporting'),
            'monthly'  => Translator::translate('Monthly', 'reporting')
        ];

        $this->addDecoratedElement(new Flatpickr(), 'text', 'start', [
            'required'         => true,
            'label'            => Translator::translate('Start', 'reporting'),
            'placeholder'      => Translator::translate('Choose date and time', 'reporting'),
            'data-enable-time' => true
        ]);

        $this->addElement('select', 'frequency', [
            'required'  => true,
            'label'     => Translator::translate('Frequency', 'reporting'),
            'options'   => [null => Translator::translate('Please choose', 'reporting')] + $frequency,
        ]);

        $this->addElement('select', 'action', [
            'required'  => true,
            'label'     => Translator::translate('Action', 'reporting'),
            'options'   => [null => Translator::translate('Please choose', 'reporting')] + $this->listActions(),
            'class'     => 'autosubmit'
        ]);

        $values = $this->getValues();

        if (isset($values['action'])) {
            $config = new Form();
//            $config->populate($this->getValues());

            /** @var \Icinga\Module\Reporting\Hook\ActionHook $action */
            $action = new $values['action'];

            $action->initConfigForm($config, $this->report);

            foreach ($config->getElements() as $element) {
                $this->addElement($element);
            }
        }

        $this->addElement('submit', 'submit', [
            'label' => $this->id === null ? Translator::translate('Create Schedule', 'reporting') : Translator::translate('Update Schedule', 'reporting')
        ]);

        if ($this->id !== null) {
            $this->addElement('submit', 'remove', [
                'label'          => Translator::translate('Remove Schedule', 'reporting'),
                'class'          => 'remove-button',
                'formnovalidate' => true
            ]);

            /** @var SubmitElementInterface $remove */
            $remove = $this->getElement('remove');
            if ($remove->hasBeenPressed()) {
                $this->getDb()->delete('schedule', ['id = ?' => $this->id]);

                // Stupid cheat because ipl/html is not capable of multiple submit buttons
                $this->getSubmitButton()->setValue($this->getSubmitButton()->getButtonLabel());
                $this->valid = true;

                return;
            }
        }

        // TODO(el): Remove once ipl/html's TextareaElement sets the value as content
        foreach ($this->getElements() as $element) {
            if ($element instanceof TextareaElement && $element->hasValue()) {
                $element->setContent($element->getValue());
            }
        }
    }

    public function onSuccess()
    {
        $db = $this->getDb();

        $values = $this->getValues();

        $now = time() * 1000;

        $data = [
            'start'     => \DateTime::createFromFormat('Y-m-d H:i', $values['start'])->getTimestamp() * 1000,
            'frequency' => $values['frequency'],
            'action'    => $values['action'],
            'mtime'     => $now
        ];

        unset($values['start']);
        unset($values['frequency']);
        unset($values['action']);

        $data['config'] = json_encode($values);

        $db->beginTransaction();

        if ($this->id === null) {
            $db->insert('schedule', $data + [
                'author'    => Auth::getInstance()->getUser()->getUsername(),
                'report_id' => $this->report->getId(),
                'ctime'     => $now
            ]);
        } else {
            $db->update('schedule', $data, ['id = ?' => $this->id]);
        }

        $db->commitTransaction();
    }
}
