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
 * MarcVariableField constructor.
 */
$field = new MarcVariableField('001');
check($field->tag === '001');
$field = new MarcVariableField();
check($field->tag === '???');

/*
 * MarcVariableField::fromArray()
 * MarcVariableField::fromText()
 */
$field = new MarcControlField('001', 'ID1');
$arrayField = array('tag' => '001', 'data' => 'ID1');
$textField = '001 ID1';
check($field->equalsTo(MarcVariableField::fromArray($arrayField)));
check($field->equalsTo(MarcVariableField::fromText($textField)));

$field = new MarcDataField('111', '2', '3', array(
    new MarcSubfield('a', 'AAA'),
    new MarcSubfield('b', 'BBB')
));
$arrayField = array('tag' => '111', 'ind1' => '2', 'ind2' => '3', 'subfields' => array(
    array('code' => 'a', 'data' => 'AAA'),
    array('code' => 'b', 'data' => 'BBB')
));
$textField = '111 23$aAAA$bBBB';
check($field->equalsTo(MarcVariableField::fromArray($arrayField)));
check($field->equalsTo(MarcVariableField::fromText($textField)));

$field = new MarcDataField('444', '5', '6', array(
    new MarcSubfield('1', new MarcControlField('001', 'ID2')),
    new MarcSubfield('1', new MarcDataField('100', '2', '3', array(
        new MarcSubfield('x', 'XXX'),
        new MarcSubfield('y', 'YYY')
    )))
));
$arrayField = array('tag' => '444', 'ind1' => '5', 'ind2' => '6', 'subfields' => array(
    array('code' => '1', 'data' => array('tag' => '001', 'data' => 'ID2')),
    array('code' => '1', 'data' => array('tag' => '100', 'ind1' => '2', 'ind2' => '3',
    'subfields' => array(
        array('code' => 'x', 'data' => 'XXX'),
        array('code' => 'y', 'data' => 'YYY')
    )
))
));
$textField = '444 56$1001ID2$110023$xXXX$yYYY';
check($field->equalsTo(MarcVariableField::fromArray($arrayField)));
check($field->equalsTo(MarcVariableField::fromText($textField)));

/*
 * MarcVariableField->isControlField()
 */
$field1 = MarcVariableField::fromText('001 ID1');
$field2 = MarcVariableField::fromText('111 23$aAAA$bBBB');
check($field1->isControlField() && !$field2->isControlField());

/*
 * MarcVariableField->isDataField()
 */
$field1 = MarcVariableField::fromText('001 ID1');
$field2 = MarcVariableField::fromText('111 23$aAAA$bBBB');
check(!$field1->isDataField() && $field2->isDataField());

/*
 * MarcVariableField->equals()
 */
$field1 = MarcVariableField::fromText('001 ID1');
$field2 = MarcVariableField::fromText('001 ID2');
$field3 = MarcVariableField::fromText('001 id1');
$field4 = MarcVariableField::fromText('005 ID1');
check(MarcVariableField::equals($field1, $field1));
check(!MarcVariableField::equals($field1, $field2));
check(MarcVariableField::equals($field1, $field2, array('ignoreChars' => '/[0-9]/u')));
check(!MarcVariableField::equals($field1, $field3));
check(MarcVariableField::equals($field1, $field3, array('ignoreCase' => true)));
check(!MarcVariableField::equals($field1, $field4));

$field1 = MarcVariableField::fromText('111 23$aAAA$bBBB');
$field2 = MarcVariableField::fromText('111 23$aAAA$bBBBC');
$field3 = MarcVariableField::fromText('111 23$bBBB$aAAA');
$field4 = MarcVariableField::fromText('111 23$bBbB$aAaa');
$field5 = MarcVariableField::fromText('222 23$aAAA$bBBB');
check(MarcVariableField::equals($field1, $field1));
check(!MarcVariableField::equals($field1, $field2));
check(MarcVariableField::equals($field1, $field2, array('ignoreChars' => '/[c]/ui')));
check(!MarcVariableField::equals($field1, $field3));
check(MarcVariableField::equals($field1, $field3, array('ignoreOrder' => true)));
check(!MarcVariableField::equals($field1, $field4));
check(MarcVariableField::equals($field1, $field4,
    array('ignoreOrder' => true, 'ignoreCase' => true)));
check(!MarcVariableField::equals($field1, $field5));

$field1 = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
$field2 = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYYZ');
$field3 = MarcVariableField::fromText('444 56$110023$yYYY$xXXX$1001ID2');
$field4 = MarcVariableField::fromText('444 56$110023$yYyY$xXXx$1001id2');
$field5 = MarcVariableField::fromText('555 56$1001ID2$110023$xXXX$yYYY');
check(MarcVariableField::equals($field1, $field1));
check(!MarcVariableField::equals($field1, $field2));
check(MarcVariableField::equals($field1, $field2, array('ignoreChars' => '/[z]/ui')));
check(!MarcVariableField::equals($field1, $field3));
check(MarcVariableField::equals($field1, $field3, array('ignoreOrder' => true)));
check(!MarcVariableField::equals($field1, $field4));
check(MarcVariableField::equals($field1, $field4,
    array('ignoreOrder' => true, 'ignoreCase' => true)));
check(!MarcVariableField::equals($field1, $field5));

/*
 * MarcVariableField->getTag()
 */
$field = MarcVariableField::fromText('001 ID1');
check($field->getTag() === '001');
$field = MarcVariableField::fromText('111 23$aAAA$bBBB');
check($field->getTag() === '111');

/*
 * MarcVariableField->setTag()
 */
$field = new MarcVariableField();
$field->setTag('555');
check($field->tag === '555');

echo 'OK' . PHP_EOL;
?>
