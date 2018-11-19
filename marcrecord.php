<?php
namespace MarcRecord;

/*
 * Constants.
 */
define('MARCRECORD_DEFAULT_LEADER', '     nam  22        450 ');

/*
 * Represents a MARC record.
 */
class MarcRecord
{
    public $leader;
    public $fields;

    public function __construct($record=null)
    {
        if (!$record) {
            $this->leader = MARCRECORD_DEFAULT_LEADER;
            $this->fields = array();
        } else if (is_array($record)) {
            if (!array_key_exists('leader', $record)) {
                $this->leader = MARCRECORD_DEFAULT_LEADER;
            } else {
                $this->leader = $record['leader'];
            }

            if (!array_key_exists('fields', $record)) {
                $this->fields = array();
            } else {
                foreach ($record['fields'] as $arrayField) {
                    $this->fields[] = MarcVariableField::fromArray($arrayField);
                }
            }
        } else if ($record instanceof MarcRecord) {
            $this->leader = $record->leader;

            $this->fields = array();
            foreach ($record->fields as $field) {
                $this->fields[] = clone $field;
            }
        }
    }

    public function __clone()
    {
        foreach ($this->fields as $i => $field) {
            $this->fields[$i] = clone $field;
        }
    }

    /*
     * Creates new object from array representation.
     */
    public static function fromArray($arrayRecord)
    {
        if (!is_array($arrayRecord)) {
            throw new MarcException('invalid record');
        }

        $record = new MarcRecord();
        $record->leader = $arrayRecord['leader'];    
        foreach ($arrayRecord['fields'] as $arrayField) {
            $record->fields[] = MarcVariableField::fromArray($arrayField);
        }
        return $record;
    }

    /*
     * Returns an array representation of this record.
     */
    public function toArray()
    {
        $arrayFields = array();
        foreach ($this->fields as $field) {
            $arrayFields[] = $field->toArray();
        }
        return array('leader' => $this->leader, 'fields' => $arrayFields);
    }

    /*
     * Returns new record created from the text string.
     */
    public static function fromText($textRecord) {
        if (!is_string($textRecord)) {
            throw new MarcException('invalid record');
        }

        $record = new MarcRecord();

        $textFields = preg_split('/\n/u', $textRecord);
        foreach ($textFields as $textField) {
            if (mb_substr($textField, 0, 4) === '000 ') {
                $record->leader = mb_substr($textField, 4);
            } else if ($textField !== '') {
                $record->fields[] = MarcVariableField::fromText($textField);
            }
        }
        return $record;
    }

    /*
     * Returns a string representation of this record.
     */
    public function toText() {
        $textRecord = '000 ' . $this->leader;
        foreach ($this->fields as $field) {
            $textRecord = $textRecord . "\n" . $field->toText();
        }
        return $textRecord;
    }

    /*
     * Replaces content of the record.
     */
    public function assign($record) {
        $this->leader = &$record->leader;
        $this->fields = &$record->fields;
    }

    /*
     * Returns true if the records are equal.
     */
    public static function equals($record1, $record2, $opts=array()) {
        if ($record1 instanceof MarcRecord && $record2 instanceof MarcRecord) {
            if (mb_substr($record1->leader, 5, 7) !== mb_substr($record2->leader, 5, 7)
                || mb_substr($record1->leader, 17) !== mb_substr($record2->leader, 17))
            {
                return false;
            }
        }

        if ($record1 instanceof MarcRecord) {
            $fields1 = $record1->fields;
        } else if (is_array($record1)) {
            $fields1 = $record1;
        } else {
            return false;
        }

        if ($record2 instanceof MarcRecord) {
            $fields2 = $record2->fields;
        } else if (is_array($record2)) {
            $fields2 = $record2;
        } else {
            return false;
        }

        $countFields1 = count($fields1);
        $countFields2 = count($fields2);
        if ($countFields1 !== $countFields2) {
            return false;
        }

        $ignoreOrder = array_key_exists('ignoreOrder', $opts) && $opts['ignoreOrder'];
        if (!$ignoreOrder) {
            for ($i = 0; $i < $countFields1; $i++) {
                if (!$fields1[$i]->equalsTo($fields2[$i], $opts)) {
                    return false;
                }
            }
        } else {
            for ($i = 0; $i < $countFields1; $i++) {
                for ($j = 0; $j < $countFields2; $j++) {
                    if ($fields1[$i]->equalsTo($fields2[$j], $opts)) {
                        break;
                    }
                }
                if ($j === $countFields2) {
                    return false;
                }
                array_splice($fields2, $j, 1);
            }
         }

        return true;
    }

    /*
     * Returns true if the records are equal.
     */
    public function equalsTo($record, $opts=array()) {
        return MarcRecord::equals($this, $record, $opts);
    }

    /*
     * Returns difference between two records.
     */
    public static function diff($record1, $record2, $opts=array()) {
fwrite(STDERR, "[1]\n");
        if ($record1 instanceof MarcRecord && $record2 instanceof MarcRecord) {
            if (mb_substr($record1->leader, 5, 7) !== mb_substr($record2->leader, 5, 7)
                || mb_substr($record1->leader, 17) !== mb_substr($record2->leader, 17))
            {
                return "leaders is not equal: [$record1->leader] [$record2->leader]";
            }
        }

fwrite(STDERR, "[2]\n");
        if ($record1 instanceof MarcRecord) {
            $fields1 = $record1->fields;
        } else if (is_array($record1)) {
            $fields1 = $record1;
        } else {
            return 'record 1 is not MarcRecord';
        }

fwrite(STDERR, "[3]\n");
        if ($record2 instanceof MarcRecord) {
            $fields2 = $record2->fields;
        } else if (is_array($record2)) {
            $fields2 = $record2;
        } else {
            return 'record 2 is not MarcRecord';
        }

fwrite(STDERR, "[4]\n");
        $countFields1 = count($fields1);
        $countFields2 = count($fields2);
        if ($countFields1 !== $countFields2) {
            return "records have differen number of fields: $fields1->length $fields2->length";
        }

fwrite(STDERR, "[5]\n");
        $ignoreOrder = array_key_exists('ignoreOrder', $opts) && $opts['ignoreOrder'];
        if (!$ignoreOrder) {
            for ($i = 0; $i < $countFields1; $i++) {
                if (!$fields1[$i]->equalsTo($fields2[$i], $opts)) {
fwrite(STDERR, "[6]\n");
                    return 'Field [' . $fields1[$i]->toText() . '] not found in record2';
                }
            }
        } else {
            for ($i = 0; $i < $countFields1; $i++) {
                for ($j = 0; $j < $countFields2; $j++) {
                    if ($fields1[$i]->equalsTo($fields2[$j], $opts)) {
                        break;
                    }
                }
                if ($j === $countFields2) {
fwrite(STDERR, "[7]\n");
                    return 'Field [' . $fields1[$i]->toText() . '] not found in record2';
                }
                array_splice($fields2, $j, 1);
            }
        }
fwrite(STDERR, "[8]\n");

        return null;
    }

    /*
     * Returns difference between two records.
     */
    public function diffFrom($record, $opts=array()) {
        return MarcRecord::diff($this, $record, $opts);
    }

    /*
     * Returns number of fields in the record.
     */
    public function size()
    {
        return count($this->fields);
    }

    /*
     * Returns true if the record does not contains fields.
     */
    public function isEmpty() {
        return (count($this->fields) === 0);
    }

    /*
     * Clears all data in the record.
     */
    public function clear()
    {
        $this->leader = MARCRECORD_DEFAULT_LEADER;
        $this->fields = array();
    }

    /*
     * Removes fields and subfields not containing actual data.
     */
    public function trim() {
        for ($fieldNo = count($this->fields) - 1; $fieldNo >= 0; $fieldNo--) {
            $field = $this->fields[$fieldNo];
            if ($field instanceof MarcDataField) {
                $field->trim();
            }
            if ($field->isEmpty()) {
                array_splice($this->fields, $fieldNo, 1);
            }
        }
    }

    /*
     * Returns the position of the variable field in the record.
     */
    public function getVariableFieldIndex($variableField) {
        return array_search($variableField, $this->fields);
    }

    /*
     * Adds a variable field.
     */
    public function addVariableField($variableField)
    {
        $this->fields[] = $variableField;
    }

    /*
     * Adds a variable field when it is not empty.
     */
    public function addNonEmptyVariableField($variableField) {
        if (!$variableField->isEmpty()) {
            $this->fields[] = $variableField;
        }
    }

    /*
     * Inserts a variable field at the specified position.
     */
    public function insertVariableField($index, $variableField) {
        if ($index < 0 || $index > count($this->fields)) {
            throw new MarcException('invalid position specified');
        }
        array_splice($this->fields, $index, 0, [$variableField]);
    }

    /*
     * Removes a list of variable fields.
     */
    public function removeVariableFields($variableFields)
    {
        foreach ($variableFields as $variableField) {
            $this->removeVariableField($variableField);
        }
    }

    /*
     * Removes a variable field.
     */
    public function removeVariableField($variableField) {
        if (!($variableField instanceof MarcVariableField)) {
            $index = intval($variableField);
            if (!is_numeric($variableField) || $index < 0 || $index >= count($this->fields)) {
                throw new MarcException('invalid field specified: ' . $variableField);
            }
            array_splice($this->fields, $index, 1);
            return;
        }

        for (;;) {
            $index = array_search($variableField, $this->fields);
            if ($index === false) {
                break;
            }
            array_splice($this->fields, $index, 1);
        }
    }

    /*
     * Returns a list of variable fields.
     */
    public function getVariableFields($tags=null)
    {
        if (!$tags) {
            return $this->fields;
        }

        $tagList = is_array($tags) ? $tags : array($tags);
        $fields = array();
        foreach ($this->fields as $field) {
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $field->tag)
                    || $tag === $field->tag)
                {
                    $fields[] = $field;
                    break;
                }
            }
        }
        return $fields;
    }

    /*
     * Returns a variable field.
     */
    public function getVariableField($tags=null)
    {
        if (!$tags) {
            return count($this->fields) > 0 ? $this->fields[0] : null;
        }

        $tagList = is_array($tags) ? $tags : array($tags);
        $fields = array();
        foreach ($this->fields as $field) {
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $field->tag)
                    || $tag === $field->tag)
                {
                    return $field;
                }
            }
        }
        return null;
    }

    /*
     * Returns a list of control fields.
     */
    public function getControlFields($tags=null) {
        $tagList = !$tags ? null : (is_array($tags) ? $tags : array($tags));

        $fields = [];
        foreach ($this->fields as $field) {
            if (!($field instanceof MarcControlField)) {
                continue;
            }
            if (!$tagList) {
                $fields[] = $field;
                continue;
            }
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $field->tag)
                    || $tag === $field->tag)
                {
                    $fields[] = $field;
                    break;
                }
            }
        }
        return $fields;
    }

    /*
     * Returns a list of data fields.
     */
    public function getDataFields($tags=null, $ind1=null, $ind2=null, $opts=array()) {
        $tagList = !$tags ? null : (is_array($tags) ? $tags : array($tags));

        $normalizeIndicators = array_key_exists('normalizeIndicators', $opts)
            && $opts['normalizeIndicators'];
        if ($normalizeIndicators) {
            if ($ind1) {
                $ind1 = preg_replace('/#/u', ' ', $ind1);
            }
            if ($ind2) {
                $ind2 = preg_replace('/#/u', ' ', $ind2);
            }
        }

        $fields = array();
        foreach ($this->fields as $field) {
            if (!($field instanceof MarcDataField)) {
                continue;
            }
            if ($normalizeIndicators) {
                if ($ind1 && preg_replace('/#/u', ' ', $field->ind1) !== $ind1
                    || $ind2 && preg_replace('/#/u', ' ', $field->ind2) !== $ind2)
                {
                    continue;
                }
            } else {
                if ($ind1 && $field->ind1 !== $ind1 || $ind2 && $field->ind2 !== $ind2) {
                    continue;
                }
            }
            if (!$tagList) {
                $fields[] = $field;
                continue;
            }
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $field->tag)
                    || $tag === $field->tag)
                {
                    $fields[] = $field;
                    break;
                }
            }
        }
        return $fields;
    }

    /*
     * Returns data of the first control field, found by field tag.
     */
    public function getControlFieldData($tags=null) {
        $field = $this->getVariableField($tags);
        if (!($field && $field instanceof MarcControlField)) {
            return null;
        }
        return $field->data;
    }

    /*
     * Returns the control number field or null if no control.
     */
    public function getControlNumberField() {
        foreach ($this->fields as $field) {
            if ($field instanceof MarcControlField && $field->tag === '001') {
                return $field;
            }
        }
        return null;
    }

    /*
     * Returns the control number or null if no control number is available.
     */
    public function getControlNumber() {
        $field = $this->getControlNumberField();
        return $field ? $field->data : null;
    }

    /*
     * Returns first subfield, found by field tags and subfield codes.
     */
    public function getSubfield($tags, $codes=null) {
        if (!$tags) {
            throw new MarcException('tags must be specified');
        }

        $tagList = is_array($tags) ? $tags : array($tags);
        foreach ($this->fields as $field) {
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $field->tag)
                    || $tag === $field->tag)
                {
                    return $field->getSubfield($codes);
                }
            }
        }
        return null;
    }

    /*
     * Returns data of the first subfield, found by field tags and subfield codes.
     */
    public function getSubfieldData($tags, $codes=null) {
        if (!$tags) {
            throw new MarcException('tags must be specified');
        }

        $tagList = is_array($tags) ? $tags : array($tags);
        foreach ($this->fields as $field) {
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $field->tag)
                    || $tag === $field->tag)
                {
                    return $field->getSubfieldData($codes);
                }
            }
        }
        return null;
    }

    /*
     * Returns first regular subfield, found by subfield codes and data pattern
     * in the first specified field.
     */
    public function getRegularSubfield($tags, $codes=null, $pattern=null) {
        if (!$tags) {
            throw new Error('tags must be specified');
        }

        $tagList = is_array($tags) ? $tags : array($tags);
        foreach ($this->fields as $field) {
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $field->tag)
                    || $tag === $field->tag)
                {
                    return $field->getRegularSubfield($codes, $pattern);
                }
            }
        }
        return null;
    }

    /*
     * Returns data of first regular subfield, found by subfield codes
     * and data pattern in the first specified field.
     */
    public function getRegularSubfieldData($tags, $codes=null, $pattern=null) {
        if (!$tags) {
            throw new Error('tags must be specified');
        }

        $tagList = is_array($tags) ? $tags : array($tags);
        foreach ($this->fields as $field) {
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $field->tag)
                    || $tag === $field->tag)
                {
                    return $field->getRegularSubfieldData($codes, $pattern);
                }
            }
        }
        return null;
    }

    /*
     * Returns the Leader.
     */
    public function &getLeader()
    {
        return $this->leader;
    }

    /*
     * Sets the Leader.
     */
    public function setLeader($leader) {
        $this->leader = $leader;
    }

    /*
     * Reorders fields according to its tags or callback function.
     */
    public function sort($callback=null) {
        if ($callback && is_callable($callback)) {
            usort($this->fields, $callback);
            return;
        }

        $origFields = $this->fields;
        usort($this->fields, function ($a, $b) use ($origFields) {
            // We don't want to reorder fields with the same tags.
            if ($a->tag === $b->tag) {
                return array_search($a, $origFields) - array_search($b, $origFields);
            }
            return $a->tag < $b->tag ? -1 : 1;
        });
    }
}

/*
 * Абстрактное поле MARC-записи.
 */
class MarcVariableField
{
    public $tag;

    public function __construct($tag='???')
    {
        $this->tag = $tag;
    }

    /*
     * Creates new object from array representation.
     */
    public static function fromArray($arrayField)
    {
        if (array_key_exists('data', $arrayField)) {
            return MarcControlField::fromArray($arrayField);
        } else {
            return MarcDataField::fromArray($arrayField);
        }
    }

    /*
     * Returns new field created from the text string.
     */
    public static function fromText($textField) {
        if (!is_string($textField)) {
            throw new MarcException('invalid field');
        }

        $tag = mb_substr($textField, 0, 3);
        if (!preg_match('/^[0-9]{3}$/u', $tag)) {
            throw new MarcException('invalid field');
        }

        if ($tag >= '001' && $tag <= '009') {
            return new MarcControlField($tag, mb_substr($textField, 4));
        }

        $ind1 = mb_substr($textField, 4, 1);
        $ind2 = mb_substr($textField, 5, 1);
        if (mb_strlen($ind1) !== 1 || mb_strlen($ind2) !== 1) {
            throw new MarcException('invalid field');
        }

        $field = new MarcDataField($tag, $ind1, $ind2);
        $textSubfieldGroups = preg_split('/(?=\$1)/ui',
            mb_substr($textField, 6), 0, PREG_SPLIT_NO_EMPTY);
        foreach ($textSubfieldGroups as $textSubfieldGroup) {
            if (mb_substr($textSubfieldGroup, 1, 1) === '1') {
                $field->subfields[] = MarcSubfield::fromText($textSubfieldGroup);
            } else {
                $textSubfields = preg_split('/(?=\$[0-9a-z])/ui',
                    $textSubfieldGroup, 0, PREG_SPLIT_NO_EMPTY);
                foreach ($textSubfields as $textSubfield) {
                    $field->subfields[] = MarcSubfield::fromText($textSubfield);
                }
            }
        }
        return $field;
    }

    /*
     * Always returns false (stub).
     */
    public function isControlField() {
        return false;
    }

    /*
     * Always returns false (stub).
     */
    public function isDataField() {
        return false;
    }

    /*
     * Returns true if the fields are equal.
     */
    public static function equals($field1, $field2, $opts=array()) {
        if ($field1 instanceof MarcControlField) {
            return MarcControlField::equals($field1, $field2, $opts);
        } else {
            return MarcDataField::equals($field1, $field2, $opts);
        }
    }

    /*
     * Returns the tag name.
     */
    public function &getTag() {
        return $this->tag;
    }

    /*
     * Sets the tag name.
     */
    public function setTag($tag) {
        $this->tag = $tag;
    }
}

/*
 * Управляющее поле MARC-записи.
 */
class MarcControlField extends MarcVariableField
{
    public $data;

    public function __construct($tag='???', $data='')
    {
        $this->tag = $tag;
        $this->data = $data;
    }

    /*
     * Creates new object from array representation.
     */
    public static function fromArray($arrayField)
    {
        return new MarcControlField($arrayField['tag'], $arrayField['data']);
    }

    /*
     * Returns an array representation of this field.
     */
    public function toArray()
    {
        return array('tag' => $this->tag, 'data' => $this->data);
    }

    /*
     * Returns a string representation of this control field.
     */
    public function toText() {
        if (!$this->data) {
            return $this->tag;
        } else {
            return $this->tag . ' ' . $this->data;
        }
    }

    /*
     * Replaces content of the field.
     */
    public function assign($field)
    {
        if (!($field instanceof MarcControlField)) {
            return false;
        }
        $this->tag = &$field->tag;
        $this->data = &$field->data;
        return true;
    }

    /*
     * Always returns true.
     */
    public function isControlField()
    {
        return true;
    }

    /*
     * Returns true if the fields are equal.
     */
    public static function equals($field1, $field2, $opts=array()) {
        if (!($field1 instanceof MarcControlField) || !($field2 instanceof MarcControlField)) {
            return false;
        }
        if ($field1->tag !== $field2->tag) {
            return false;
        }

        $data1 = $field1->data;
        $data2 = $field2->data;
        if (array_key_exists('ignoreCase', $opts) && $opts['ignoreCase']) {
            $data1 = mb_strtoupper($data1);
            $data2 = mb_strtoupper($data2);
        }
        if (array_key_exists('ignoreChars', $opts)) {
            $data1 = preg_replace($opts['ignoreChars'], '', $data1);
            $data2 = preg_replace($opts['ignoreChars'], '', $data2);
        }
        return ($data1 === $data2);
    }

    /*
     * Returns true if the fields are equal.
     */
    public function equalsTo($field, $opts=array()) {
        return MarcControlField::equals($this, $field, $opts);
    }

    /*
     * Returns true if the field does not contains data.
     */
    public function isEmpty() {
        return !$this->data;
    }

    /*
     * Returns the data element.
     */
    public function &getData() {
        return $this->data;
    }

    /*
     * Sets the data element.
     */
    public function setData($data) {
        $this->data = $data;
    }
}

/*
 * Поле данных MARC-записи.
 */
class MarcDataField extends MarcVariableField
{
    public $ind1;
    public $ind2;
    public $subfields;

    public function __construct($tag='???', $ind1=' ', $ind2=' ', $subfields=array())
    {
        $this->tag = $tag;
        $this->ind1 = $ind1;
        $this->ind2 = $ind2;
        $this->subfields = $subfields;
    }

    public function __clone()
    {
        foreach ($this->subfields as $i => $subfield) {
            $this->subfields[$i] = clone $subfield;
        }
    }

    /*
     * Creates new object from array representation.
     */
    public static function fromArray($arrayField)
    {
        $field = new MarcDataField($arrayField['tag'], $arrayField['ind1'], $arrayField['ind2']);
        foreach ($arrayField['subfields'] as $arraySubfield) {
            $field->subfields[] = MarcSubfield::fromArray($arraySubfield);
        }
        return $field;
    }

    /*
     * Returns an array representation of this field.
     */
    public function toArray()
    {
        $arraySubfields = array();
        foreach ($this->subfields as $subfield) {
            $arraySubfields[] = $subfield->toArray();
        }
        return array('tag' => $this->tag, 'ind1' => $this->ind1, 'ind2' => $this->ind2,
            'subfields' => $arraySubfields);
    }

    /*
     * Returns a string representation of this data field.
     */
    public function toText() {
        $textField = $this->tag . ' ' . $this->ind1 . $this->ind2;
        foreach ($this->subfields as $subfield) {
            $textField = $textField . $subfield->toText();
        }
        return $textField;
    }

    /*
     * Replaces content of the field.
     */
    public function assign($field) {
        if (!($field instanceof MarcDataField)) {
            return false;
        }
        $this->tag = &$field->tag;
        $this->ind1 = &$field->ind1;
        $this->ind2 = &$field->ind2;
        $this->subfields = &$field->subfields;
        return true;
    }

    /*
     * Always returns true.
     */
    public function isDataField() {
        return true;
    }

    /*
     * Returns true if the fields are equal.
     */
    public static function equals($field1, $field2, $opts=array()) {
        if ($field1 instanceof MarcDataField && $field2 instanceof MarcDataField) {
            if ($field1->tag !== $field2->tag) {
                return false;
            }
            if (!array_key_exists('normalizeIndicators', $opts) || !$opts['normalizeIndicators']) {
                if ($field1->ind1 !== $field2->ind1 || $field1->ind2 !== $field2->ind2) {
                    return false;
                }
            }
            if (str_replace('#', ' ', $field1->ind1) !== str_replace('#', ' ', $field2->ind1)
                || str_replace('#', ' ', $field1->ind2) !== str_replace('#', ' ', $field2->ind2))
            {
                return false;
            }
        }

        if ($field1 instanceof MarcDataField) {
            $subfields1 = $field1->subfields;
        } else if (is_array($field1)) {
            $subfields1 = $field1;
        } else {
            return false;
        }

        if ($field2 instanceof MarcDataField) {
            $subfields2 = $field2->subfields;
        } else if (is_array($field2)) {
            $subfields2 = $field2;
        } else {
            return false;
        }

        $countSubfields1 = count($subfields1);
        $countSubfields2 = count($subfields2);
        if ($countSubfields1 !== $countSubfields2) {
            return false;
        }

        $ignoreOrder = array_key_exists('ignoreOrder', $opts) && $opts['ignoreOrder'];
        if (!$ignoreOrder) {
            for ($i = 0; $i < $countSubfields1; $i++) {
                if (!$subfields1[$i]->equalsTo($subfields2[$i], $opts)) {
                    return false;
                }
            }
        } else {
            for ($i = 0; $i < $countSubfields1; $i++) {
                for ($j = 0; $j < $countSubfields2; $j++) {
                    if ($subfields1[$i]->equalsTo($subfields2[$j], $opts)) {
                        break;
                    }
                }
                if ($j === $countSubfields2) {
                    return false;
                }
                unset($subfields2[$j]);
            }
        }

        return true;
    }

    /*
     * Returns true if the fields are equal.
     */
    public function equalsTo($field, $opts=array()) {
        return MarcDataField::equals($this, $field, $opts);
    }

    /*
     * Returns number of subfields in the field.
     */
    public function size() {
        return count($this->subfields);
    }

    /*
     * Returns true if the field does not contains subfields.
     */
    public function isEmpty() {
        return (count($this->subfields) === 0);
    }

    /*
     * Removes subfields not containing actual data.
     */
    public function trim() {
        for ($subfieldNo = count($this->subfields) - 1; $subfieldNo >= 0; $subfieldNo--) {
            $subfield = $this->subfields[$subfieldNo];
            if ($subfield->data instanceof MarcDataField) {
                $subfield->data->trim();
            }
            if ($subfield->isEmpty()) {
                array_splice($this->subfields, $subfieldNo, 1);
            }
        }
    }

    /*
     * Returns the position of the subfield in the field.
     */
    public function getSubfieldIndex($subfield) {
        return array_search($subfield, $this->subfields);
    }

    /*
     * Returns the first indicator.
     */
    public function &getIndicator1() {
        return $this->ind1;
    }

    /*
     * Sets the first indicator.
     */
    public function setIndicator1($ind1) {
        $this->ind1 = $ind1;
    }

    /*
     * Returns the second indicator.
     */
    public function &getIndicator2() {
        return $this->ind2;
    }

    /*
     * Sets the second indicator.
     */
    public function setIndicator2($ind2) {
        $this->ind2 = $ind2;
    }

    /*
     * Returns the list of subfields for the given subfield codes.
     */
    public function getSubfields($codes=null) {
        if (!$codes) {
            return $this->subfields;
        }

        $codeList = is_array($codes) ? $codes : array($codes);
        $subfields = array();
        foreach ($this->subfields as $subfield) {
            if (in_array($subfield->code, $codeList)) {
                $subfields[] = $subfield;
            }
        }
        return $subfields;
    }

    /*
     * Returns the first subfield for the given subfield codes.
     */
    public function getSubfield($codes=null) {
        if (!$codes) {
            return count($this->subfields) > 0 ? $this->subfields[0] : null;
        }

        $codeList = is_array($codes) ? $codes : array($codes);
        foreach ($this->subfields as $subfield) {
            if (in_array($subfield->code, $codeList)) {
                return $subfield;
            }
        }
        return null;
    }

    /*
     * Returns the data of first subfield for the given subfield codes.
     */
    public function getSubfieldData($codes=null) {
        if (!$codes) {
            return count($this->subfields) > 0 ? $this->subfields[0]->data : null;
        }

        $codeList = is_array($codes) ? $codes : array($codes);
        foreach ($this->subfields as $subfield) {
            if (in_array($subfield->code, $codeList)) {
                return $subfield->data;
            }
        }
        return null;
    }

    /*
     * Returns the list of regular subfields for the given subfield codes and data value.
     */
    public function getRegularSubfields($codes=null, $pattern=null) {
        $codeList = !$codes ? null : (is_array($codes) ? $codes : array($codes));
        $subfields = array();
        foreach ($this->subfields as $subfield) {
            if (!($subfield->data instanceof MarcVariableField)
                && (!$codeList || in_array($subfield->code, $codeList)))
            {
                if (!$pattern
                    || (mb_substr($pattern, 0, 1) === '/' && preg_match($pattern, $subfield->data))
                    || $subfield->data === $pattern)
                {
                    $subfields[] = $subfield;
                }
            }
        }
        return $subfields;
    }

    /*
     * Returns the first regular subfield for the given subfield codes
     * and data pattern.
     */
    public function getRegularSubfield($codes=null, $pattern=null) {
        $codeList = !$codes ? null : (is_array($codes) ? $codes : array($codes));
        foreach ($this->subfields as $subfield) {
            if (!($subfield->data instanceof MarcVariableField)
                && (!$codeList || in_array($subfield->code, $codeList)))
            {
                if (!$pattern
                    || (mb_substr($pattern, 0, 1) === '/' && preg_match($pattern, $subfield->data))
                    || $subfield->data === $pattern)
                {
                    return $subfield;
                }
            }
        }
        return null;
    }

    /*
     * Returns the data of first regular subfield for the given subfield codes
     * and data pattern.
     */
    public function getRegularSubfieldData($codes=null, $pattern=null) {
        $codeList = !$codes ? null : (is_array($codes) ? $codes : array($codes));
        foreach ($this->subfields as $subfield) {
            if (!($subfield->data instanceof MarcVariableField)
                && (!$codeList || in_array($subfield->code, $codeList)))
            {
                if (!$pattern
                    || (mb_substr($pattern, 0, 1) === '/' && preg_match($pattern, $subfield->data))
                    || $subfield->data === $pattern)
                {
                    return $subfield->data;
                }
            }
        }
        return null;
    }

    /*
     * Returns a list of embedded variable fields.
     */
    public function getVariableFields($tags=null) {
        $tagList = !$tags ? null : (is_array($tags) ? $tags : array($tags));
        $fields = array();
        foreach ($this->subfields as $subfield) {
            if (!($subfield->data instanceof MarcVariableField)) {
                continue;
            }
            if (!$tagList) {
                $fields[] = $subfield->data;
                continue;
            }
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $subfield->data->tag)
                    || $tag === $subfield->data->tag)
                {
                    $fields[] = $subfield->data;
                    break;
                }
            }
        }
        return $fields;
    }

    /*
     * Returns an embedded variable field.
     */
    public function getVariableField($tags=null) {
        $tagList = !$tags ? null : (is_array($tags) ? $tags : array($tags));
        foreach ($this->subfields as $subfield) {
            if (!($subfield->data instanceof MarcVariableField)) {
                continue;
            }
            if (!$tagList) {
                return $subfield->data;
            }
            foreach ($tagList as $tag) {
                if (mb_substr($tag, 0, 1) === '/' && preg_match($tag, $subfield->data->tag)
                    || $tag === $subfield->data->tag)
                {
                    return $subfield->data;
                }
            }
        }
        return null;
    }

    /*
     * Adds subfields.
     */
    public function addSubfields($subfields) {
        foreach ($subfields as $subfield) {
            $this->subfields[] = $subfield;
        }
    }

    /*
     * Adds a subfield.
     */
    public function addSubfield($subfield) {
        $this->subfields[] = $subfield;
    }

    /*
     * Adds a subfield if value is not empty.
     */
    public function addNonEmptySubfield($subfield) {
        if (!$subfield->isEmpty()) {
            $this->subfields[] = $subfield;
        }
    }

    /*
     * Inserts subfields at the specified position.
     */
    public function insertSubfields($index, $subfields) {
        if ($index < 0 || $index > count($this->subfields)) {
            throw new MarcException('invalid position specified');
        }
        array_splice($this->subfields, $index, 0, $subfields);
    }

    /*
     * Inserts subfield at the specified position.
     */
    public function insertSubfield($index, $subfield) {
        if ($index < 0 || $index > count($this->subfields)) {
            throw new MarcException('invalid position specified');
        }
        array_splice($this->subfields, $index, 0, array($subfield));
    }

    /*
     * Removes a list of subfields.
     */
    public function removeSubfields($subfields) {
        foreach ($subfields as $subfield) {
            $this->removeSubfield($subfield);
        }
    }

    /*
     * Removes a subfield.
     */
    public function removeSubfield($subfield) {
        if (!($subfield instanceof MarcSubfield)) {
            $index = intval($subfield);
            if (!is_numeric($subfield) || $index < 0 || $index >= count($this->subfields)) {
                throw new MarcException('invalid subfield specified: ' . $subfield);
            }
            array_splice($this->subfields, $index, 1);
            return;
        }

        for (;;) {
            $index = array_search($subfield, $this->subfields);
            if ($index === false) {
                break;
            }
            array_splice($this->subfields, $index, 1);
        }
    }

    /*
     * Returns data of the first embedded control field, found by field tag.
     */
    public function getControlFieldData($tags=null) {
        $field = $this->getVariableField($tags);
        if (!($field && $field instanceof MarcControlField)) {
            return null;
        }
        return $field->data;
    }

    /*
     * Returns the control number embedded field or null if no control.
     */
    public function getControlNumberField() {
        $field = $this->getVariableField('001');
        if (!($field && $field instanceof MarcControlField)) {
            return null;
        }
        return $field;
    }

    /*
     * Returns the control number from embedded control field
     * or null if no control number is available.
     */
    public function getControlNumber() {
        return $this->getControlFieldData('001');
    }

    /*
     * Adds an embedded variable field.
     */
    public function addVariableField($index, $field=null) {
        if ($index instanceof MarcVariableField) {
            $this->subfields[] = new MarcSubfield('1', $index);
        } else if ($field instanceof MarcVariableField) {
            array_splice($this->subfields, $index, 0, [new MarcSubfield('1', $field)]);
        } else {
            throw new MarcException('invalid type of embedded field');
        }
    }

    /*
     * Removes a list of embedded variable fields.
     */
    public function removeVariableFields($variableFields) {
        foreach ($variableFields as $variableField) {
            $this->removeVariableField($variableField);
        }
    }

    /*
     * Removes an embedded variable field.
     */
    public function removeVariableField($variableField) {
        $index = null;
        if (!($variableField instanceof MarcVariableField)) {
            $index = intval($variableField);
            if (!is_numeric($variableField) || $index < 0) {
                throw new MarcException('invalid field specified: ' . $variableField->toText());
            }
        }

        $embeddedFieldNo = 0;
        foreach ($this->subfields as $subfieldKey => $subfield) {
            if (!($subfield->data instanceof MarcVariableField)) {
                continue;
            }

            if ($index !== null) {
                if ($embeddedFieldNo === $index) {
                    array_splice($this->subfields, $subfieldKey, 1);
                    break;
                }
            } else if ($subfield->data === $variableField) {
                array_splice($this->subfields, $subfieldKey, 1);
            }

            $embeddedFieldNo++;
        }
    }
}

/*
 * Подполе MARC-записи.
 */
class MarcSubfield
{
    public $code;
    public $data;

    public function __construct($code='?', $data='')
    {
        $this->code = $code;
        $this->data = $data;
    }

    /*
     * Creates new object from array representation.
     */
    public static function fromArray($arraySubfield)
    {
        if (is_array($arraySubfield['data'])) {
            return new MarcSubfield(
                $arraySubfield['code'], MarcVariableField::fromArray($arraySubfield['data']));
        }
        return new MarcSubfield($arraySubfield['code'], $arraySubfield['data']);
    }

    /*
     * Returns an array representation of this subfield.
     */
    public function toArray()
    {
        return array('code' => $this->code, 'data' => $this->data);
    }

    /*
     * Returns new subfield created from the text string.
     */
    public static function fromText($textSubfield) {
        if (!is_string($textSubfield) || !preg_match('/^\$[0-9a-z]/ui', $textSubfield)) {
            throw new MarcException('invalid subfield');
        }

        $code = mb_substr($textSubfield, 1, 1);
        $data = mb_substr($textSubfield, 2);
        if ($code === '1') {
            $data = mb_substr($textSubfield, 2, 3) . ' ' . mb_substr($textSubfield, 5);
            return new MarcSubfield($code, MarcVariableField::fromText($data));
        }
        return new MarcSubfield($code, mb_substr($textSubfield, 2));
    }

    /*
     * Returns a string representation of this subfield.
     */
    public function toText() {
        $textSubfield = '$' . $this->code;
        if (!($this->data instanceof MarcVariableField)) {
            if (count($this->data) > 0) {
                $textSubfield = $textSubfield . $this->data;
            }
        } else if ($this->data) {
            $textEmbeddedField = $this->data->toText();
            $textSubfield = $textSubfield
                . mb_substr($textEmbeddedField, 0, 3) . mb_substr($textEmbeddedField, 4);
        }
        return $textSubfield;
    }

    /*
     * Replaces content of the subfield.
     */
    public function assign($subfield) {
        if (!($subfield instanceof MarcSubfield)) {
            return false;
        }
        $this->code = &$subfield->code;
        $this->data = &$subfield->data;
        return true;
    }

    /*
     * Returns true if the subfields are equal.
     */
    public static function equals($subfield1, $subfield2, $opts=array()) {
        if (!($subfield1 instanceof MarcSubfield) || !($subfield2 instanceof MarcSubfield)) {
            return false;
        }
        if ($subfield1->code !== $subfield2->code) {
            return false;
        }
        if ($subfield1->data instanceof MarcVariableField) {
            return $subfield1->data->equalsTo($subfield2->data, $opts);
        }

        $data1 = $subfield1->data;
        $data2 = $subfield2->data;
        if (array_key_exists('ignoreCase', $opts) && $opts['ignoreCase']) {
            $data1 = mb_strtoupper($data1);
            $data2 = mb_strtoupper($data2);
        }
        if (array_key_exists('ignoreChars', $opts)) {
            $data1 = preg_replace($opts['ignoreChars'], '', $data1);
            $data2 = preg_replace($opts['ignoreChars'], '', $data2);
        }
        return ($data1 === $data2);
    }

    /*
     * Returns true if the subfields are equal.
     */
    public function equalsTo($subfield, $opts=array())
    {
        return MarcSubfield::equals($this, $subfield, $opts);
    }

    /*
     * Returns true if the subfield does not contains data.
     */
    public function isEmpty() {
        if ($this->data instanceof MarcVariableField) {
            return $this->data->isEmpty();
        }
        return !$this->data;
    }

    /*
     * Returns true if subfield is embedded field.
     */
    public function isEmbeddedField() {
        return $this->data instanceof MarcVariableField;
    }

    /*
     * Returns the data element identifier.
     */
    public function &getCode() {
        return $this->code;
    }

    /*
     * Sets the data element identifier.
     */
    public function setCode($code) {
        $this->code = $code;
    }

    /*
     * Returns the data element.
     */
    public function &getData() {
        return $this->data;
    }

    /*
     * Sets the data element.
     */
    public function setData($data) {
        $this->data = $data;
    }
}

/*
 * Exception class
 */
class MarcException extends \Exception
{
}

?>
