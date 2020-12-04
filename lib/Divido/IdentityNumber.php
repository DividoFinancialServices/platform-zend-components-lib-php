<?php
class Divido_IdentityNumber
{

    //
    // READ: You have to restart the project after making changes to
    //       this file
    //
    // Different identityNumber depending on country
    //
    // - SE, YYMMDDXXXX
    // - NO, DDMMYYXXXXX
    // - DK, DDMMYYXXXX
    // - FI, DDMMYY-XXX# [1]
    //
    // [1]: The finnish configuration is not perfect as of now. We can
    //      currently not use `react-number-format` on the front end
    //      to control what value we get. This because finnish personal
    //      idenity numbers also can hold a character (A-Z), see # above.
    //      What does this mean?
    //      It means that we have to check the value, and parse it before
    //      talking to Collector or others.
    //
    // For the other countries, SE/NO/DK, we know that the formatting we get
    // from the front end is accepted.
    //
    // TODO: Look into a solution for [1], where we can format the finnish
    // personal identity number aswell. I created an issue on `react-number-format`
    // here; <https://github.com/s-yadav/react-number-format/issues/84>
    //

    static function getIdentityNumberConfiguration($country) {

        switch ($country) {
            case 'SE':
                $identityNumberConfiguration = [
                    'component' => 'Input',
                    'name' => 'identityNumber',
                    'fullWidth' => true,
                    'props' => [
                        'type' => 'identityNumber',
                        'label' => 'inputs.identityNumber.label',
                        'placeholder' => 'inputs.identityNumber.placeholder',
                        'format' => '######-####',
                    ],
                    'validation' => [
                        'rules' => [
                            'required' => true,
                            'length' => 10,
                        ],
                    ],
                ];
                break;
            case 'NO':
                $identityNumberConfiguration = [
                    'component' => 'Input',
                    'name' => 'identityNumber',
                    'fullWidth' => true,
                    'props' => [
                        'type' => 'identityNumber',
                        'label' => 'inputs.identityNumber.label',
                        'placeholder' => 'inputs.identityNumber.placeholder',
                        'format' => '###########',
                    ],
                    'validation' => [
                        'rules' => [
                            'required' => true,
                            'length' => 11,
                        ],
                    ],
                ];
                break;
            case 'DK':
                $identityNumberConfiguration = [
                    'component' => 'Input',
                    'name' => 'identityNumber',
                    'fullWidth' => true,
                    'props' => [
                        'type' => 'identityNumber',
                        'label' => 'inputs.identityNumber.label',
                        'placeholder' => 'inputs.identityNumber.placeholder',
                        'format' => '######-####',
                    ],
                    'validation' => [
                        'rules' => [
                            'required' => true,
                            'length' => 10,
                        ],
                    ],
                ];
                break;
            case 'FI':
                $identityNumberConfiguration = [
                    'component' => 'Input',
                    'name' => 'identityNumber',
                    'fullWidth' => true,
                    'props' => [
                        'type' => 'text',
                        'label' => 'inputs.identityNumber.label',
                        'placeholder' => 'inputs.identityNumber.placeholder',
                    ],
                    'validation' => [
                        'rules' => [
                            'required' => true,
                            'pattern' => '^(0[1-9]|[12][0-9]|3[01])(0[1-9]|1[012])(\d{2})[-A](\d{3})(\d|[A-Z])$',
                        ],
                    ],
                ];
                break;
            default:
                $identityNumberConfiguration = [
                    'component' => 'Input',
                    'name' => 'identityNumber',
                    'fullWidth' => true,
                    'props' => [
                        'type' => 'text',
                        'label' => 'inputs.identityNumber.label',
                        'placeholder' => 'inputs.identityNumber.placeholder',
                    ],
                    'validation' => [
                        'rules' => [
                            'required' => true,
                            'length' => 12,
                        ],
                    ],
                ];
                break;
        }

        return $identityNumberConfiguration;

    }

}
?>
