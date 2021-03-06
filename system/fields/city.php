<?php
/**
 * Назвавние city оставлено для совместимости
 */
class fieldCity extends cmsFormField {

    public $title       = LANG_PARSER_CITY;
    public $is_public   = true;
    public $sql         = 'int(11) UNSIGNED NULL DEFAULT NULL';
    public $cache_sql   = 'varchar(128) NULL DEFAULT NULL';
    public $filter_type = 'int';
    public $var_type    = 'integer';
    public $is_denormalization = true;

    public function getOptions(){

        return array(
            new fieldList('location_type', array(
                'title'   => LANG_PARSER_CITY_LOCATION_TYPE,
                'default' => 'cities',
                'items'   => array(
                    'countries' => LANG_COUNTRY,
                    'regions'   => LANG_REGION,
                    'cities'    => LANG_CITY
                )
            )),
            new fieldString('location_group', array(
                'title' => LANG_PARSER_CITY_LOCATION_GROUP,
                'hint'  => LANG_PARSER_CITY_LOCATION_GROUP_HINT,
                'rules' => array(
                    array('sysname'),
                    array('max_length', 20)
                )
            )),
            new fieldString('output_string', array(
                'title' => LANG_PARSER_CITY_OUTPUT_STRING,
                'hint'  => LANG_PARSER_CITY_OUTPUT_STRING_HINT
            ))
        );

    }

    public function parse($value){
        $output_string = $this->getOption('output_string');
        if($output_string){
            $output_string = str_replace('}', cmsFormField::FIELD_CACHE_POSTFIX.'}', $output_string);
            return htmlspecialchars(string_replace_keys_values($output_string, $this->item));
        }
        return htmlspecialchars($this->item[$this->getDenormalName()]);
    }

    public function storeCachedValue($value){

        return $this->getLocationTypeValue($value, $this->getOption('location_type'));

    }

    private function getLocationTypeValue($id, $location_type){

        $model = new cmsModel();

        $item_name = $model->getField('geo_'.$location_type, $id, 'name');

        if($item_name){
            return $item_name;
        }

        return null;

    }

    public function getInput($value){

        $model = cmsCore::getModel('geo');

        $location_group = $this->getOption('location_group');
        $location_type  = $this->getOption('location_type');

        $this->data['items'] = array();

        // если поля не объеденены и это поле выбора города
        if(!$location_group && $location_type == 'cities'){

            $city_name = $this->getLocationTypeValue($value, $location_type);

            $value = array(
                'id'   => $value,
                'name' => $city_name
            );

        } elseif($location_type == 'countries'){

            $this->data['items'] = array('0'=>'') + $model->getCountries();

        } elseif(!$location_group &&  $location_type == 'regions'){

            $this->data['items'] = array('0'=>'') + $model->getRegions();

        }

        $this->data['location_group'] = $location_group;
        $this->data['location_type']  = $location_type;

        $this->data['dom_attr'] = array(
            'id'             => $this->id,
            'data-selected'  => (is_array($value) ? $value['id'] : $value),
            'data-type'      => $location_type,
            'data-child'     => ($location_type == 'countries' ? 'regions' : 'cities'),
            'data-items-url' => href_to('geo', 'get_items')
        );

        return parent::getInput($value);

    }

    public function applyFilter($model, $value) {
        return $model->filterEqual($this->name, "{$value}");
    }

}
