<?php


namespace Concludis\ApiClient\Common;


use Concludis\ApiClient\Util\ArrayUtil;

trait TraitParamsEndpoint {


    protected array $params = [];

    abstract public function paramsDefinition(): array;

    protected function castParam(array $def, $val) {

        if(array_key_exists('cast', $def)) {

            $cast = $def['cast'];
            if($cast === 'int') {
                return (int)$val;
            }
            if($cast === 'int[]') {
                return ArrayUtil::toIntArray((array)$val);
            }
            if($cast === 'string') {
                return (string)$val;
            }
            if($cast === 'string[]') {
                return ArrayUtil::toStringArray((array)$val);
            }
            if($cast === 'bool') {
                return (bool)$val;
            }
            if($cast === 'object') {
                return (object)$val;
            }
            if($cast === 'array') {
                return (array)$val;
            }
        }

        return $val;
    }

    public function addParam(string $param_key, $param_val): self {

        $params_definition = $this->paramsDefinition();

        if(!array_key_exists($param_key, $params_definition)) {
            return $this;
        }

        $def = (array)($params_definition[$param_key]);

        $this->params[$param_key] = $this->castParam($def, $param_val);

        return $this;
    }

}