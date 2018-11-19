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
 * MarcControlField constructor.
 */
$field = new MarcControlField('001', 'ID1');
check($field->tag === '001' && $field->data === 'ID1');

$field1 = new MarcControlField('001', 'ID1');
$field2 = clone $field1;
check($field1 !== $field2 && $field1->equalsTo($field2));

/*
 * MarcControlField->assign()
 */
$field1 = MarcVariableField::fromText('001 ID1');
$field2 = MarcVariableField::fromText('001 ID2');
$field1->assign($field2);
$field1->data = 'ID3';
check($field1 !== $field2 && $field1->equalsTo($field2));

/*
 * MarcControlField::equals()
 * MarcControlField->equalsTo()
 */
$field1 = MarcVariableField::fromText('001 ID1');
$field2 = MarcVariableField::fromText('001 ID2');
$field3 = MarcVariableField::fromText('001 id1');
$field4 = MarcVariableField::fromText('005 ID1');

check(MarcControlField::equals($field1, $field1));
check(!MarcControlField::equals($field1, $field2));
check(MarcControlField::equals($field1, $field2, array('ignoreChars' => '/[0-9]/u')));
check(!MarcControlField::equals($field1, $field3));
check(MarcControlField::equals($field1, $field3, array('ignoreCase' => true)));
check(!MarcControlField::equals($field1, $field4));

check($field1->equalsTo($field1));
check(!$field1->equalsTo($field2));
check($field1->equalsTo($field2, array('ignoreChars' => '/[0-9]/u')));
check(!$field1->equalsTo($field3));
check($field1->equalsTo($field3, array('ignoreCase' => true)));
check(!$field1->equalsTo($field4));

/*
 * MarcControlField->isEmpty()
 */
$field1 = MarcVariableField::fromText('001 ID1');
$field2 = new MarcControlField('001', 'text');
$field3 = new MarcControlField('001', 'text');
$field4 = new MarcControlField('001');
$field5 = new MarcControlField('001', null);
$field6 = new MarcControlField('001', '');

check(!$field1->isEmpty());
check(!$field2->isEmpty());
check(!$field3->isEmpty());
check($field4->isEmpty());
check($field5->isEmpty());
check($field6->isEmpty());

/*
 * MarcControlField->getData()
 */
$field = MarcVariableField::fromText('001 ID1');
check($field->getData() === 'ID1' && $field->getData() === $field->data);

$field = MarcVariableField::fromText('001 ID1');
$data = $field->getData();
$data = 'ID2';
check($field->getData() === 'ID1');

$field = MarcVariableField::fromText('001 ID1');
$data = &$field->getData();
$data = 'ID2';
check($field->getData() === 'ID2');

/*
 * MarcControlField->setData()
 */
$field = new MarcControlField('001');
$field->setData('ID1');
check($field->data === 'ID1');

/*
 * MarcControlField->toText()
 */
$field = new MarcControlField('001', 'ID1');
check($field->toText() === '001 ID1');

echo 'OK' . PHP_EOL;
?>
