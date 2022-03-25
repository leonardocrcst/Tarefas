<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use stdClass;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    public function struct(Model $model): array
    {
        $table = $model->getTable();
        $list = [];
        foreach (DB::select("SHOW FULL COLUMNS FROM $table") as $field) {
            $obj = new stdClass();
            $this->setDataType($field, $obj);
            $obj->auto = str_contains($field->Extra, "auto_increment");
            $obj->nullable = $field->Null === "YES";
            $this->setKey($table, $field, $obj);
            $this->setCaption($field, $obj);
            $obj->default = $field->Default;
            $this->setHidden($model, $field, $obj);
            $this->setRequired($obj);
            $list[$field->Field] = $obj;
        }
        return $list;
    }

    private function setDataType(stdClass $field, stdClass $obj)
    {
        $temp = explode("(", $field->Type);
        $obj->datatype = $temp[0];
        if (count($temp) > 1) {
            $this->setLength(str_replace(")", "", $temp[1]), $obj);
        }
    }

    private function setKey(string $table, stdClass $field, stdClass $obj)
    {
        if ($field->Key !== "") {
            $obj->key = $field->Key;
            if ($field->Key === "MUL") {
                $this->foreignKey($table, $field, $obj);
            }
        }
    }

    private function foreignKey(string $table, stdClass $field, stdClass $obj)
    {
        $dbase = getenv("DB_DATABASE");
        $data = DB::table("information_schema.KEY_COLUMN_USAGE")
            ->where("TABLE_NAME", "=", $table)
            ->where("TABLE_SCHEMA", "=", $dbase)
            ->where("COLUMN_NAME", "=", $field->Field)
            ->get([
                "REFERENCED_TABLE_SCHEMA as schema",
                "REFERENCED_TABLE_NAME as table",
                "CONSTRAINT_NAME as caption",
                "REFERENCED_COLUMN_NAME as value"
            ])->toArray()[0];
        $name = explode("_", $data->caption);
        $data->caption = $name[count($name) - 1];
        $obj->foreign = $data;
    }

    private function setLength(string $length, $obj)
    {
        $foo = explode(" ", $length);
        $obj->unsigned = count($foo) > 1;
        $temp = explode(',', $foo[0]);
        $obj->length = intval($temp[0]);
        if (count($temp) > 1) {
            $obj->fraction = intval($foo[0]);
        }
    }

    private function setCaption(stdClass $field, stdClass $obj)
    {
        $obj->caption = null;
        $obj->label = null;
        $caption = json_decode($field->Comment);
        if (!json_last_error()) {
            $obj->caption = isset($caption->caption) ?? null;
            $obj->label = isset($caption->label) ?? null;
        }
    }

    private function setRequired(stdClass $obj)
    {
        $obj->required = true;
        if ($obj->auto || $obj->nullable || !is_null($obj->default)) {
            $obj->required = false;
        }
    }

    private function setHidden(Model $model, mixed $field, stdClass $obj)
    {
        $obj->hidden = false;
        if (in_array($field->Field, $model->getHidden()) || $obj->auto) {
            $obj->hidden = true;
        }
    }
}
