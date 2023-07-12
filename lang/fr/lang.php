<?php

return [
    'plugin' => [
        'name' => 'workflow',
        'description' => 'No description provided yet...',
    ],
    'permissions' => [
        'some_permission' => 'Some permission',
    ],
    'base' => [
        'btn_workflow' => 'Sauver et...',
        'change_state' => 'Changer d\'état',
        'confirm_no_trans_title' => 'Transitions interdites',
        'error' => [
            'trans_not_found' => 'Impossible de trouver la transition :t_name sur le modèle :m_name avec le Wf :wf_name '
        ],
        'popup' => [
            'confirm_title' => 'Confirmation',
            'next_place_label' => 'Prochain état : ',
            'save_no_transition' => 'Votre modèle sera sauvé sans appliquer de transition.',
            'transition' => 'Transition : '
        ],
        'list_histo_states' => 'Historique des états',
        'must_trans' => 'Transition obligatoire',
        'no_change_state' => 'Ne pas changer d\'état',
        'state' => 'Etat',
        'state_change_forbidden' => 'Changement état interdit'
    ],
];
