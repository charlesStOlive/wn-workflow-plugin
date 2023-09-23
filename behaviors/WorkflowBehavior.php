<?php

namespace Waka\Workflow\Behaviors;

use Backend\Classes\ControllerBehavior;
use Redirect;
use Backend;
use Winter\Storm\Router\Helper as RouterHelper;
use Session;
use Str;

class WorkflowBehavior extends ControllerBehavior
{
    public $controller;
    // public $workflowWidget;
    public $model;


    /**
     * @var array|object
     */
    public $config;


    /**
     * @var array
     */
    private $modelWorkflowData;

    public $user;
    /**
     * @inheritDoc
     */
    // protected $requiredProperties = ['workflowConfig'];

    /**
     * @var array Configuration values that must exist when applying the primary config file.
     */
    // protected $requiredConfig = ['places'];

    public $popupAfterSave;

    /**
     * @inheritDoc
     */


    /**
     * @var mixed Configuration for this behaviour
     */
    public $workflowConfig = 'config_waka.yaml';

    /**
     * @var array Configuration values that must exist when applying the primary config file.
     */
    protected $requiredConfig = ['workflow'];

    //protected $workflowWidget;

    public function __construct($controller)
    {
        //trace_log('initialisation du behavior');
        parent::__construct($controller);
        $this->controller = $controller;
        $this->user = $controller->user;
        //
        $this->config = $this->makeConfig($controller->workflowConfig ?: $this->workflowConfig, $this->requiredConfig);
        $this->config->modelClass = Str::normalizeClassName($this->config->modelClass);

        //
        \Event::listen('waka.workflow.popup_afterSave', function ($data) {
            \Session::put('popup_afterSave', $data);
        });
        \Event::listen('waka.wutils.btns.replace_action_btn', function ($model) {
            return $this->addButtonsToBtnsWidget($model);
        });
        \Event::listen('controller.btns.action_bar.hide_delete', function ($model) {
            return $this->deleteButonShouldBeHidded($model);
        });
        $wfPopupAfterSave = \Session::get('popup_afterSave');
        if ($wfPopupAfterSave) {
            $this->addJs('/plugins/waka/wutils/assets/js/popup_after.js');
        }
    }


    private function parseWorkflowData($model)
    {
        if ($this->modelWorkflowData) {
            return $this->modelWorkflowData;
        } else {
            $this->modelWorkflowData = [
                'form_auto_config' => $model->listWfPlaceFormAuto(),
                'transitions' => $this->getWorkFlowTransitions(),
                'must_trans' => $model->wfMustTrans,
                'no_delete' => $model->wfNoDelete,
            ];
            // trace_log('this->modelWorkflowData!',$this->modelWorkflowData);
            return $this->modelWorkflowData;
        }
    }

    public function deleteButonShouldBeHidded($model) {
        $workflowConfigState = $this->parseWorkflowData($model);
        if($workflowConfigState['no_delete'] ?? false) {
            return 'hide';
        } else {
            return;
        }
    }

    public function addButtonsToBtnsWidget($model)
    {

        $hasWorkflow = $this->config->workflow;
        if (!$hasWorkflow) {
            return;
        }
        $workflowConfigState = $this->getWorkflowConfigFromState($model);

        if (!$model->userHasWfPermission()) {
            return $this->makePartial('btns/no_wf_role');
        }

        //BLOCK
        $block = $workflowConfigState['block'] ?? false;
        if ($block) {
            return $this->makePartial('btns/no_wf_role');
        }

        $modelWorkflowData = $this->parseWorkflowData($model);
        //
        //Recuperation de toutes les transitions autorisés.
        $transitions = $modelWorkflowData['transitions'];
        //trace_log($transitions);
        $hide_all_trans = $workflowConfigState['hide_all_trans'] ?? false;
        //Si hide all trans on vide le tableau des transitions.
        if ($hide_all_trans) {
            $transitions = [];
        }
        //On va determiner si il y a des formAuto dans la config ou le workflow du model si il y en a on les enregistrent. 
        $wfTrysFromFormAuto = [];
        $wfTrysFromFormAuto = $workflowConfigState['form_auto'] ?? [];
        if (!count($wfTrysFromFormAuto)) {
            $wfTrysFromFormAuto = $modelWorkflowData['form_auto_config'];
        }
        //
        $change_trans = $workflowConfigState['change_trans'] ?? [];
        $separate_all = $workflowConfigState['separate_all'] ?? false;
        //----------
        $wfBtns = [];
        $wfBtnsBefore = [];
        $wfBtnsAfter = [];
        //nettoyage des transitions avec  wfTrysFromFormAuto
        foreach ($transitions as $i => $transition) {
            $transKey = $transition['value'];
            if (in_array($transition['value'], $wfTrysFromFormAuto)) {
                unset($transitions[$i]);
            }
        }
        //Réorganisation des transitions
        if ($change_trans) {
            foreach ($transitions as $i => $transition) {
                if ($ch = $change_trans[$transKey] ?? false) {
                    if ($ch['properties'] ?? false) {
                        $newProperties = [];
                        foreach ($ch['properties'] as $skey => $prop) {
                            $newProperties[$skey] = \Lang::get($prop);
                        }
                        //trace_log($newProperties);
                        $transitions[$i] = array_merge($transitions[$i], $newProperties);
                    }
                    if ($view = $ch['view'] ?? null) {
                        if ($view == 'btns_before') {
                            array_push($wfBtnsBefore, $transition);
                            unset($transitions[$i]);
                        } else if ($view == 'btns_after') {
                            array_push($wfBtnsAfter, $transition);
                            unset($transitions[$i]);
                        }
                    }
                }
            }
        }
        $wfBtns = $transitions;
        if ($separate_all) {
            $wfBtnsBefore =  array_merge($wfBtnsBefore, $wfBtns);
            $wfBtns = [];
        }


        $this->vars['modelClass'] = $this->config->modelClass;
        $this->vars['user'] = $this->user = \BackendAuth::getUser();

        $this->vars['mustTrans'] =  /*XXXXXXXXXXX*/ $model->wfMustTrans;
        $this->vars['separateFirst'] =  $workflowConfigState['separateFirst'] ?? false;
        $this->vars['modelId'] = $model->id;

        $this->vars['wfTrys'] = $wfTrysFromFormAuto ? "try:'" . implode(',', $wfTrysFromFormAuto) . "'" : null;
        $this->vars['wfBtns'] = $wfBtns;
        $this->vars['wfBtnsBefore'] = $wfBtnsBefore;
        $this->vars['wfBtnsAfter'] = $wfBtnsAfter;

        return $this->makePartial('workflow');
    }

    /**
     * Controller accessor for making partials within this behavior.
     * @param string $partial
     * @param array $params
     * @return string Partial contents
     */
    public function workflowMakePartial($partial, $params = [])
    {
        $contents = $this->controller->makePartial('workflow_' . $partial, $params + $this->vars, false);
        if (!$contents) {
            $contents = $this->makePartial($partial, $params);
        }

        return $contents;
    }

    public function getWorkflowConfigFromState($model)
    {
        //trace_log($this->config->workflow);
        $state = $model->state;
        $stateConfig = $this->config->workflow[$state] ?? null;
        if ($stateConfig) {
            return $stateConfig;
        } else {
            // \Log::error('pas de config pour le state ' . $state);
            return [];
        }
    }

    public function getWorkFlowTransitions($withHidden = false)
    {

        $model = $this->controller->formGetModel();
        $transitions =  $model->getWakaWorkflow()->getEnabledTransitions($model);
        $objTransition = [];
        foreach ($transitions as $transition) {
            $transitionMeta = $model->wakaWorkflowGetTransitionMetadata($transition);
            $hidden = $transitionMeta['hidden'] ?? false;
            if (!$hidden) {
                $name = $transition->getName();
                $label = $transitionMeta['label'] ?? null;
                $button = $transitionMeta['button'] ?? null;
                $buton = $button ? $button : $label;
                $com = $transitionMeta['com'] ?? null;
                $redirect = $transitionMeta['redirect'] ?? null;
                $icon = $transitionMeta['icon'] ?? null;
                $color = $transitionMeta['color'] ?? null;
                $object = [
                    'value' => $name,
                    'label' => \Lang::get($buton),
                    'com' => $com,
                    'icon' => $icon,
                    'color' => $color,
                    'redirect' => $redirect,
                ];
                array_push($objTransition, $object);
            }
        }
        return $objTransition;
    }

    public function listInjectRowClass($record, $value)
    {
        if ($this->config->workflow_options['row_no_permission_allowed'] ?? false) return;
        //
        if (!$record->userHasWfPermission()) {
            return 'nolink  disabled';
        }
    }

    public function relationExtendConfig($config, $field, $model)
    {
        //trace_log('relationExtendConfig');
        $fieldsReadOnly = $model->getWfROFields();
        if (!count($fieldsReadOnly)) {
            return;
        }
        //trace_log($fieldsReadOnly);
        if (in_array($field, $fieldsReadOnly)) {
            $config->view['toolbarButtons'] = false;
            $config->view['showCheckboxes'] = false;
            $config->view['recordOnClick'] = null;

            $config->manage = [];
        }
    }



    public function formExtendFields($form)
    {
        $model = $form->model;
        if ($model) {
            $fieldsToHide = $model->getWfHiddenFields();
            foreach ($fieldsToHide as $field) {
                $form->removeField($field);
            }
            $fieldsReadOnly = $model->getWfROFields();
            foreach ($fieldsReadOnly as $field) {
                $roField = $form->getField($field);
                if ($roField) {
                    $roField->readOnly = true;
                    if($roField->type == 'widget') {
                        $roField->disabled = true;
                    }
                }
            }
        }
    }


    public function formBeforeSave($model)
    {
        //trace_log("formBeforeSave");
        //trace_log(post());
        if (!$model->userHasWfPermission()) {
            throw new \ValidationException(['error' => "Vous n'avez pas le droit d'enregistrer dans l'état actuel"]);
        } else {
            if (post('change_state') != '') {
                $model->change_state = post('change_state');
            }
            if (post('try') != '') {
                //trace_log('Il y a un try : '.post('try'));
                $model->change_state = post('try');
            }
        }
    }

    public function update_onSave($recordId = null, $context = null)
    {
        //trace_log("update_onSave---------------------");
        if (post('try')) {
            $this->controller->asExtension('FormController')->update_onSave($recordId, $context);
            $redirect = \Session::pull('wf_redirect');
            $model = $this->controller->formFindModelObject($recordId);
            //trace_log("REDIRECTION : " . $redirect);
            if ($redirect == "refresh:1" || !$redirect) {
                return Redirect::refresh();
            }
            $redirectUrl = null;
            if ($redirect == "close:1") {
                $redirectUrl = $this->controller->formGetRedirectUrl('update-close', $model);
            }

            if ($redirect == "redirect:1") {
                $redirectUrl = $this->controller->formGetRedirectUrl($context, $model);
            }
            //trace_log($redirectUrl);
            return Backend::redirect($redirectUrl);
        } else {
            return $this->controller->asExtension('FormController')->update_onSave($recordId, $context);
        }
    }
}
