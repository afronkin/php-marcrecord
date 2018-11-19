<?php
namespace MarcRecord;

require_once(__DIR__ . '/../marcrecord.php');

function check($value)
{
    if (!$value) {
        throw new \Exception('Check failed');
    }
}

mb_internal_encoding('UTF-8');
/*
 * MarcRecord constructor.
 */
$record = new MarcRecord();
check(strlen($record->leader) === 24 && count($record->fields) === 0);

$record = new MarcRecord(array(
    'leader' => '     caa  22        450 ',
    'fields' => array(
        array('tag' => '001', 'data' => 'ID1'),
        array('tag' => '111', 'ind1' => '1', 'ind2' => '2', 'subfields' => array(
            array('code' => 'a', 'data' => 'AAA'),
            array('code' => '1', 'data' => array(
                'tag' => '222', 'ind1' => '3', 'ind2' => '4', 'subfields' => array(
                    array('code' => 'b', 'data' => 'BBB'),
                    array('code' => 'c', 'data' => 'CCC')
                )
            ))
        ))
    )
));
check(mb_strlen($record->leader) === 24
    && mb_substr($record->leader, 5, 3) === 'caa'
    && count($record->fields) === 2
    && $record->fields[0] instanceof MarcControlField
    && $record->fields[0]->tag === '001'
    && $record->fields[0]->data === 'ID1'
    && $record->fields[1] instanceof MarcDataField
    && $record->fields[1]->tag === '111'
    && $record->fields[1]->ind1 === '1'
    && $record->fields[1]->ind2 === '2'
    && count($record->fields[1]->subfields) === 2
    && $record->fields[1]->subfields[0] instanceof MarcSubfield
    && $record->fields[1]->subfields[0]->code === 'a'
    && $record->fields[1]->subfields[0]->data === 'AAA'
    && $record->fields[1]->subfields[1] instanceof MarcSubfield
    && $record->fields[1]->subfields[1]->code === '1'
    && $record->fields[1]->subfields[1]->data instanceof MarcDataField
    && $record->fields[1]->subfields[1]->data->tag === '222'
    && $record->fields[1]->subfields[1]->data->ind1 === '3'
    && $record->fields[1]->subfields[1]->data->ind2 === '4'
    && count($record->fields[1]->subfields[1]->data->subfields) === 2
    && $record->fields[1]->subfields[1]->data->subfields[0] instanceof MarcSubfield
    && $record->fields[1]->subfields[1]->data->subfields[0]->code === 'b'
    && $record->fields[1]->subfields[1]->data->subfields[0]->data === 'BBB'
    && $record->fields[1]->subfields[1]->data->subfields[1]->code === 'c'
    && $record->fields[1]->subfields[1]->data->subfields[1]->data === 'CCC'
);

$record1 = new MarcRecord(array(
    'leader' => '     caa  22        450 ',
    'fields' => array(
        array('tag' => '001', 'data' => 'ID1'),
        array('tag' => '111', 'ind1' => '1', 'ind2' => '2', 'subfields' => array(
            array('code' => 'a', 'data' => 'AAA'),
            array('code' => 'b', 'data' => 'BBB')
        ))
    )
));
$record2 = new MarcRecord($record1);
check(mb_strlen($record2->leader) === 24
    && mb_substr($record2->leader, 5, 3) === 'caa'
    && count($record2->fields) === 2
    && $record2->fields[0] instanceof MarcControlField
    && $record2->fields[0]->tag === '001'
    && $record2->fields[0]->data === 'ID1'
    && $record2->fields[1] instanceof MarcDataField
    && $record2->fields[1]->tag === '111'
    && $record2->fields[1]->ind1 === '1'
    && $record2->fields[1]->ind2 === '2'
    && count($record2->fields[1]->subfields) === 2
    && $record2->fields[1]->subfields[0] instanceof MarcSubfield
    && $record2->fields[1]->subfields[0]->code === 'a'
    && $record2->fields[1]->subfields[0]->data === 'AAA'
    && $record2->fields[1]->subfields[1] instanceof MarcSubfield
    && $record2->fields[1]->subfields[1]->code === 'b'
    && $record2->fields[1]->subfields[1]->data === 'BBB'
);
$record3 = new MarcRecord($record1);
$record3->leader = '     naa  22        450 ';
$record3->fields[0]->data = 'ID2';
$record3->fields[1]->subfields[0]->data = 'ZZZ';
check(mb_substr($record1->leader, 5, 3) === 'caa'
    && mb_substr($record3->leader, 5, 3) === 'naa'
    && $record1->fields[0]->data === 'ID1'
    && $record3->fields[0]->data === 'ID2'
    && $record1->fields[1]->subfields[0]->data === 'AAA'
    && $record3->fields[1]->subfields[0]->data === 'ZZZ'
);

/*
 * MarcRecord::fromArray()
 * MarcRecord::fromText()
 */
$record = MarcRecord::fromArray(array(
    'leader' => '     caa  22        450 ',
    'fields' => array(
        array('tag' => '001', 'data' => 'ID1'),
        array('tag' => '111', 'ind1' => '1', 'ind2' => '2', 'subfields' => array(
            array('code' => 'a', 'data' => 'AAA'),
            array('code' => '1', 'data' => array(
                'tag' => '222', 'ind1' => '3', 'ind2' => '4', 'subfields' => array(
                    array('code' => 'b', 'data' => 'BBB'),
                    array('code' => 'c', 'data' => 'CCC')
                )
            ))
        ))
    )
));
check(mb_strlen($record->leader) === 24
    && mb_substr($record->leader, 5, 3) === 'caa'
    && count($record->fields) === 2
    && $record->fields[0] instanceof MarcControlField
    && $record->fields[0]->tag === '001'
    && $record->fields[0]->data === 'ID1'
    && $record->fields[1] instanceof MarcDataField
    && $record->fields[1]->tag === '111'
    && $record->fields[1]->ind1 === '1'
    && $record->fields[1]->ind2 === '2'
    && count($record->fields[1]->subfields) === 2
    && $record->fields[1]->subfields[0] instanceof MarcSubfield
    && $record->fields[1]->subfields[0]->code === 'a'
    && $record->fields[1]->subfields[0]->data === 'AAA'
    && $record->fields[1]->subfields[1] instanceof MarcSubfield
    && $record->fields[1]->subfields[1]->code === '1'
    && $record->fields[1]->subfields[1]->data instanceof MarcDataField
    && $record->fields[1]->subfields[1]->data->tag === '222'
    && $record->fields[1]->subfields[1]->data->ind1 === '3'
    && $record->fields[1]->subfields[1]->data->ind2 === '4'
    && count($record->fields[1]->subfields[1]->data->subfields) === 2
    && $record->fields[1]->subfields[1]->data->subfields[0] instanceof MarcSubfield
    && $record->fields[1]->subfields[1]->data->subfields[0]->code === 'b'
    && $record->fields[1]->subfields[1]->data->subfields[0]->data === 'BBB'
    && $record->fields[1]->subfields[1]->data->subfields[1]->code === 'c'
    && $record->fields[1]->subfields[1]->data->subfields[1]->data === 'CCC'
);

$record = MarcRecord::fromText("000      caa  22        450 \n"
    . "001 ID1\n"
    . "111 12\$aAAA\$122234\$bBBB\$cCCC\n");
check(mb_strlen($record->leader) === 24
    && mb_substr($record->leader, 5, 3) === 'caa'
    && count($record->fields) === 2
    && $record->fields[0] instanceof MarcControlField
    && $record->fields[0]->tag === '001'
    && $record->fields[0]->data === 'ID1'
    && $record->fields[1] instanceof MarcDataField
    && $record->fields[1]->tag === '111'
    && $record->fields[1]->ind1 === '1'
    && $record->fields[1]->ind2 === '2'
    && count($record->fields[1]->subfields) === 2
    && $record->fields[1]->subfields[0] instanceof MarcSubfield
    && $record->fields[1]->subfields[0]->code === 'a'
    && $record->fields[1]->subfields[0]->data === 'AAA'
    && $record->fields[1]->subfields[1] instanceof MarcSubfield
    && $record->fields[1]->subfields[1]->code === '1'
    && $record->fields[1]->subfields[1]->data instanceof MarcDataField
    && $record->fields[1]->subfields[1]->data->tag === '222'
    && $record->fields[1]->subfields[1]->data->ind1 === '3'
    && $record->fields[1]->subfields[1]->data->ind2 === '4'
    && count($record->fields[1]->subfields[1]->data->subfields) === 2
    && $record->fields[1]->subfields[1]->data->subfields[0] instanceof MarcSubfield
    && $record->fields[1]->subfields[1]->data->subfields[0]->code === 'b'
    && $record->fields[1]->subfields[1]->data->subfields[0]->data === 'BBB'
    && $record->fields[1]->subfields[1]->data->subfields[1]->code === 'c'
    && $record->fields[1]->subfields[1]->data->subfields[1]->data === 'CCC'
);

/*
 * MarcRecord->assign()
 */
$record1 = MarcRecord::fromText("001 ID1\n111 23\$aAAA\$bBBB");
$record2 = MarcRecord::fromText("111 23\$bBBB\$aAAA\n001 ID1");
$record1->assign($record2);
check($record1 !== $record2 && $record1->equalsTo($record2));

/*
 * MarcRecord->equalsTo()
 */
$record1 = MarcRecord::fromText("001 ID1\n111 23\$aAAA\$bBBB");
$record2 = MarcRecord::fromText("111 23\$bBBB\$aAAA\n001 ID1");
$record3 = MarcRecord::fromText("111 23\$bbBb\$aAaA\n001 ID1");
$record4 = MarcRecord::fromText("001 ID1\n444 56\$1001ID2\$110023\$xXXX\$yYYY");
$record5 = MarcRecord::fromText("444 56\$110023\$yYYY\$xXXX\$1001ID2\n001 ID1");

check($record1->equalsTo($record1));
check($record1->equalsTo(clone $record1));

check(!$record1->equalsTo($record2));
check($record1->equalsTo($record2, array('ignoreOrder' => true)));

check(!$record2->equalsTo($record3));
check($record2->equalsTo($record3, array('ignoreCase' => true)));
check($record1->equalsTo($record3, array('ignoreOrder' => true, 'ignoreCase' => true)));

check($record4->equalsTo(clone $record4));
check(!$record4->equalsTo($record5));
check($record4->equalsTo($record5, array('ignoreOrder' => true)));

/*
 * MarcRecord->diffFrom()
 */
$record1 = MarcRecord::fromText("001 ID1\n111 23\$aAAA\$bBBB");
$record2 = MarcRecord::fromText("111 23\$bBBB\$aAAA\n001 ID1");
$record3 = MarcRecord::fromText("111 23\$bbBb\$aAaA\n001 ID1");
$record4 = MarcRecord::fromText("001 ID1\n444 56\$1001ID2\$110023\$xXXX\$yYYY");
$record5 = MarcRecord::fromText("444 56\$110023\$yYYY\$xXXX\$1001ID2\n001 ID1");

check($record1->diffFrom($record1) === null);
check($record1->diffFrom(clone $record1) === null);

check($record1->diffFrom($record2) === 'Field [001 ID1] not found in record2');
check($record1->diffFrom($record2, array('ignoreOrder' => true)) === null);

check($record2->diffFrom($record3) === 'Field [111 23$bBBB$aAAA] not found in record2');
check($record2->diffFrom($record3, array('ignoreCase' => true)) === null);
check($record1->diffFrom($record3, array('ignoreOrder' => true, 'ignoreCase' => true)) === null);

check($record4->diffFrom(clone $record4) === null);
check($record4->diffFrom($record5) === 'Field [001 ID1] not found in record2');
check($record4->diffFrom($record5, array('ignoreOrder' => true)) === null);

/*
 * MarcRecord->size()
 */
$record1 = new MarcRecord();
$record2 = MarcRecord::fromText("001 ID1");
$record3 = MarcRecord::fromText("001 ID1\n111 23\$aAAA\$bBBB");
check($record1->size() === 0 && $record2->size() === 1 && $record3->size() === 2);

/*
 * MarcRecord->isEmpty()
 */
$record1 = new MarcRecord();
$record2 = MarcRecord::fromText("001 ID1");
check($record1->isEmpty() && !$record2->isEmpty());

/*
 * MarcRecord->clear()
 */
$record = MarcRecord::fromText("001 ID1");
$record->clear();
check(count($record->fields) === 0);

/*
 * MarcRecord->trim()
 */
$record = MarcRecord::fromText("001 ID1\n005 \n111 23\$aAAA\$b\$cCCC\$d");
$record->trim();
check(count($record->fields) === 2 && count($record->fields[1]->subfields) === 2);

/*
 * MarcRecord->getVariableFieldIndex()
 */
$record = MarcRecord::fromText("001 ID1\n005 \n111 23\$aAAA\$b\$cCCC\$d");
check($record->getVariableFieldIndex($record->fields[0]) === 0);
check($record->getVariableFieldIndex($record->fields[1]) === 1);

/*
 * MarcRecord->addVariableField()
 */
$record = new MarcRecord();
$record->addVariableField(new MarcControlField('001', 'ID1'));
$record->addVariableField(new MarcDataField('200', ' ', ' ', array(new MarcSubfield('a', 'AAA'))));
check(count($record->fields) === 2);

$record = new MarcRecord();
$field = new MarcControlField('001', 'ID1');
$record->addVariableField($field);
$field->data = 'ID2';
check($record->fields[0]->data === 'ID2');

/*
 * MarcRecord->addNonEmptyVariableField()
 */
$record = new MarcRecord();
$record->addNonEmptyVariableField(new MarcControlField('001', 'ID1'));
$record->addNonEmptyVariableField(new MarcControlField('005', ''));
$record->addNonEmptyVariableField(new MarcControlField('007', null));
$record->addNonEmptyVariableField(new MarcDataField('200', ' ', ' ',
    array(new MarcSubfield('a', 'AAA'))));
$record->addNonEmptyVariableField(new MarcDataField('200'));
check(count($record->fields) === 2);

/*
 * MarcRecord->insertVariableField()
 */
$record = new MarcRecord();
$record->insertVariableField(0, new MarcControlField('001', 'ID1'));
$record->insertVariableField(0, new MarcDataField('200', ' ', ' ',
    array(new MarcSubfield('a', 'AAA'))));
$record->insertVariableField(1, new MarcControlField('005', ''));
$record->insertVariableField($record->size(), new MarcControlField('007', null));

try {
  $record->insertVariableField(-1, new MarcControlField('002', '2'));
  throw new \Exception('check failed');
} catch (MarcException $e) {
}
try {
  $record->insertVariableField($record->size() + 1, new MarcControlField('002', '2'));
  throw new \Exception('check failed');
} catch (MarcException $e) {
}

check(count($record->fields) === 4
  && $record->fields[0]->tag === '200' && $record->fields[1]->tag === '005'
  && $record->fields[2]->tag === '001' && $record->fields[3]->tag === '007');

/*
 * MarcRecord->removeVariableFields()
 */
$record = MarcRecord::fromText("001 ID1\n111 23\$aAAA\$bBBB\n222 34\$cCCC\$dDDD");
$record->removeVariableFields($record->getVariableFields('111'));
check(count($record->fields) === 2);
$record->removeVariableFields(array(1));
check(count($record->fields) === 1);
$record->removeVariableFields($record->getVariableFields());
check(count($record->fields) === 0);

/*
 * MarcRecord->removeVariableField()
 */
$record = MarcRecord::fromText("001 ID1\n111 23\$aAAA\$bBBB\n222 34\$cCCC\$dDDD");
$record->removeVariableField($record->fields[0]);
check(count($record->fields) === 2);
$record->removeVariableField(1);
check(count($record->fields) === 1);
$record->removeVariableField($record->fields[0]);
check(count($record->fields) === 0);

/*
 * MarcRecord->getVariableFields()
 */
$record = MarcRecord::fromText(
  "001 ID1\n111 23\$aAAA\$bBBB\n111 23\$cCCC\$dDDD\n122 34\$eEEE\$fFFF");
check(count($record->getVariableFields()) === 4);
check(count($record->getVariableFields('001')) === 1);
check(count($record->getVariableFields('111')) === 2);
check(count($record->getVariableFields('333')) === 0);
check(count($record->getVariableFields('/^1..$/u')) === 3);
check(count($record->getVariableFields('/^3..$/u')) === 0);
check(count($record->getVariableFields(array('122', '001'))) === 2);
check(count($record->getVariableFields(array('/^12/u', '001', '/^0/u'))) === 2);
check(count($record->getVariableFields(array('007', '/^3/u'))) === 0);

/*
 * MarcRecord->getVariableField()
 */
$record = MarcRecord::fromText(
  "001 ID1\n111 23\$aAAA\$bBBB\n111 23\$cCCC\$dDDD\n122 34\$eEEE\$fFFF");
check($record->getVariableField()->tag === '001');
check($record->getVariableField('001')->tag === '001');
check($record->getVariableField('111')->tag === '111');
check($record->getVariableField('333') === null);
check($record->getVariableField('/^1..$/u')->tag === '111');
check($record->getVariableField('/^3..$/u') === null);
check($record->getVariableField(array('122', '001'))->tag === '001');
check($record->getVariableField(array('/^12/u', '001', '/^0/u'))->tag === '001');
check($record->getVariableField(array('007', '/^3/u')) === null);

/*
 * MarcRecord->getControlFields()
 */
$record = MarcRecord::fromText("001 ID1\n005 20160101102030.1\n111 23\$aAAA");
check(count($record->getControlFields()) === 2);
check(count($record->getControlFields('001')) === 1);
check(count($record->getControlFields('007')) === 0);
check(count($record->getControlFields('111')) === 0);
check(count($record->getControlFields('/^0../u')) === 2);
check(count($record->getControlFields('/^007/u')) === 0);
check(count($record->getControlFields(array('005', '001'))) === 2);
check(count($record->getControlFields(array('/^005/u', '001', '/^0/u'))) === 2);
check(count($record->getControlFields(array('007', '/^008/u'))) === 0);

/*
 * MarcRecord->getDataFields()
 */
$record = MarcRecord::fromText(
  "001 ID1\n111 23\$aAAA\$bBBB\n111 23\$cCCC\$dDDD\n122 34\$eEEE\$fFFF\n234 #1\$xX");

check(count($record->getDataFields()) === 4);
check(count($record->getDataFields('111')) === 2);
check(count($record->getDataFields('333')) === 0);
check(count($record->getDataFields('001')) === 0);
check(count($record->getDataFields('/^1..$/u')) === 3);
check(count($record->getDataFields('/^3..$/u')) === 0);
check(count($record->getDataFields(array('122', '001'))) === 1);
check(count($record->getDataFields(array('/^1/u', '001', '/^0/u'))) === 3);
check(count($record->getDataFields(array('007', '/^3/u'))) === 0);

check(count($record->getDataFields('122', '3')) === 1);
check(count($record->getDataFields('122', '3', '4')) === 1);
check(count($record->getDataFields('122', '1', '1')) === 0);
check(count($record->getDataFields('/^1../u', '2', '3')) === 2);

check(count($record->getDataFields('234', '#', '1')) === 1);
check(count($record->getDataFields('234', ' ', '1')) === 0);
check(count($record->getDataFields('234', ' ', '1',
  array('normalizeIndicators' => true))) === 1);

/*
 * MarcRecord->getControlFieldData()
 */
$record = MarcRecord::fromText("001 ID1\n005 20160101102030.1\n111 23\$aAAA");
check($record->getControlFieldData() === 'ID1');
check($record->getControlFieldData('001') === 'ID1');
check($record->getControlFieldData('007') === null);
check($record->getControlFieldData('111') === null);
check($record->getControlFieldData('/^0../u') === 'ID1');
check($record->getControlFieldData('/^007/u') === null);
check($record->getControlFieldData(array('005', '001')) === 'ID1');
check($record->getControlFieldData(array('/^005/u', '001', '/^0/u')) === 'ID1');
check($record->getControlFieldData(array('007', '/^008/u')) === null);

/*
 * MarcRecord->getControlNumber()
 */
$record1 = MarcRecord::fromText("001 ID1\n111 23\$aAAA");
$record2 = MarcRecord::fromText("222 34\$bBBB\n001 ID2");
check($record1->getControlNumber() === 'ID1');
check($record2->getControlNumber() === 'ID2');
check((new MarcRecord())->getControlNumber() === null);

/*
 * MarcRecord->getSubfield()
 */
$record = MarcRecord::fromText(
  "001 ID1\n111 23\$aAAA\$bBBB\n111 23\$cCCC\$dDDD\n122 34\$eEEE\$fFFF");
check($record->getSubfield('111', 'a')->data === 'AAA');
check($record->getSubfield('333', 'a') === null);
check($record->getSubfield('/^1../u', 'b')->data === 'BBB');
check($record->getSubfield('/^12./u', 'a') === null);
check($record->getSubfield(array('333', '/^12./u'), 'e')->data === 'EEE');
check($record->getSubfield('122', array('a', 'c', 'e'))->data === 'EEE');
check($record->getSubfield('122')->data === 'EEE');
try {
  $record->getSubfield(null, 'a');
  throw new \Exception('check failed');
} catch (MarcException $e) {
  check($e->getMessage() === 'tags must be specified');
}

/*
 * MarcRecord->getSubfieldData()
 */
$record = MarcRecord::fromText(
  "001 ID1\n111 23\$aAAA\$bBBB\n111 23\$cCCC\$dDDD\n122 34\$eEEE\$fFFF");
check($record->getSubfieldData('111', 'a') === 'AAA');
check($record->getSubfieldData('333', 'a') === null);
check($record->getSubfieldData('/^1../u', 'b') === 'BBB');
check($record->getSubfieldData('/^12./u', 'a') === null);
check($record->getSubfieldData(array('333', '/^12./u'), 'e') === 'EEE');
check($record->getSubfieldData('122', array('a', 'c', 'e')) === 'EEE');
check($record->getSubfieldData('122') === 'EEE');
try {
  $record->getSubfieldData(null, 'a');
  throw new \Exception('check failed');
} catch (MarcException $e) {
  check($e->getMessage() === 'tags must be specified');
}

/*
 * MarcRecord->getLeader()
 */
$record = MarcRecord::fromText("000      nam  22        450 \n001 ID1");
check($record->getLeader() === $record->leader);

$record = MarcRecord::fromText("000      nam  22        450 \n001 ID1");
$leader = &$record->getLeader();
$leader = '     caa  22        450 ';
check(mb_substr($record->leader, 5, 3) === 'caa');

/*
 * MarcRecord->setLeader()
 */
$record = new MarcRecord();
$record->setLeader('12345');
check($record->getLeader() === '12345');

/*
 * MarcRecord->sort()
 */
$record = MarcRecord::fromText(
  "222 45\$cCCC\n001 ID1\n111 23\$bBBB\n111 34\$aAAA");

$record->sort();
check($record->fields[0]->tag === '001'
  && $record->fields[1]->tag === '111' && $record->fields[1]->ind1 === '2'
  && $record->fields[2]->tag === '111' && $record->fields[2]->ind1 === '3'
  && $record->fields[3]->tag === '222');

$record->sort(function ($a, $b) {
  return $a->tag < $b->tag ? 1 : ($a->tag > $b->tag ? -1 : 0);
});
check($record->fields[0]->tag === '222' && $record->fields[1]->tag === '111'
  && $record->fields[2]->tag === '111' && $record->fields[3]->tag === '001');

echo 'OK' . PHP_EOL;
?>
