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
 * MarcDataField constructor.
 */
$field = new MarcDataField('111', '2', '3');
check($field->tag === '111' && $field->ind1 === '2' && $field->ind2 === '3');

$field = new MarcDataField('111', '2', '3', array(
    new MarcSubfield('a', 'AAA'),
    new MarcSubfield('b', 'BBB')
));
check(count($field->subfields) === 2
    && $field->subfields[0]->code === 'a' && $field->subfields[0]->data === 'AAA'
    && $field->subfields[1]->code === 'b' && $field->subfields[1]->data === 'BBB');

$field1 = new MarcDataField('111', '2', '3', array(
    new MarcSubfield('a', 'AAA'),
    new MarcSubfield('b', 'BBB'),
    new MarcSubfield('1', new MarcDataField('222', '4', '5', array(
        new MarcSubfield('c', 'CCC'),
        new MarcSubfield('d', 'DDD')
    )))
));
$field2 = clone $field1;
check($field1 !== $field2 && $field1->equalsTo($field2));
$field2->subfields[0]->code = 'x';
array_splice($field2->subfields, 1, 1);
$field2->subfields[1]->data->subfields[0]->code = 'y';
$field2->subfields[1]->data->subfields[1]->data = 'ZZZ';
check($field1 !== $field2 && !$field1->equalsTo($field2));

/*
 * MarcDataField->assign()
 */
$field1 = MarcVariableField::fromText('111 23$aAAA$bBBB');
$field2 = MarcVariableField::fromText('111 23$aAAA$bBBBC');
$field1->assign($field2);
$field1->ind1 = '4';
check($field1 !== $field2 && $field1->equalsTo($field2));

/*
 * MarcDataField::equals()
 */
$field1 = MarcVariableField::fromText('111 23$aAAA$bБББ');
$field2 = MarcVariableField::fromText('111 23$aAAA$bБББГ');
$field3 = MarcVariableField::fromText('111 23$bБББ$aAAA');
$field4 = MarcVariableField::fromText('111 23$bБбБ$aAaA');
$field5 = MarcVariableField::fromText('222 23$aAAA$bБББ');
check(MarcDataField::equals($field1, $field1));
check(!MarcDataField::equals($field1, $field2));
check(MarcDataField::equals($field1, $field2, array('ignoreChars' => '/[г]/ui')));
check(!MarcDataField::equals($field1, $field3));
check(MarcDataField::equals($field1, $field3, array('ignoreOrder' => true)));
check(!MarcDataField::equals($field1, $field4));
check(MarcDataField::equals($field1, $field4,
    array('ignoreOrder' => true, 'ignoreCase' => true)));
check(!MarcDataField::equals($field1, $field5));

$field1 = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
$field2 = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYYZ');
$field3 = MarcVariableField::fromText('444 56$110023$yYYY$xXXX$1001ID2');
$field4 = MarcVariableField::fromText('444 56$110023$yYyY$xXXx$1001id2');
$field5 = MarcVariableField::fromText('555 56$1001ID2$110023$xXXX$yYYY');
check(MarcDataField::equals($field1, $field1));
check(!MarcDataField::equals($field1, $field2));
check(MarcDataField::equals($field1, $field2, array('ignoreChars' => '/[z]/ui')));
check(!MarcDataField::equals($field1, $field3));
check(MarcDataField::equals($field1, $field3, array('ignoreOrder' => true)));
check(!MarcDataField::equals($field1, $field4));
check(MarcDataField::equals($field1, $field4,
    array('ignoreOrder' => true, 'ignoreCase' => true)));
check(!MarcDataField::equals($field1, $field5));

$field1 = MarcVariableField::fromText('111 #1$aAAA$bBBB');
$field2 = MarcVariableField::fromText('111  1$aAAA$bBBB');
check(!MarcDataField::equals($field1, $field2));
check(MarcDataField::equals($field1, $field2, array('normalizeIndicators' => true)));

/*
 * MarcDataField->size()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB');
check($field->size() === 2);
$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check($field->size() === 2);

/*
 * MarcDataField->isEmpty()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB');
check(!$field->isEmpty());
$field = new MarcDataField('111', '2', '3');
check($field->isEmpty());

/*
 * MarcDataField->trim()
 */
$field = MarcVariableField::fromText('111 23$aAAA$b$cCCC$d');
$field->trim();
check(count($field->subfields) === 2);

/*
 * MarcDataField->getSubfieldIndex()
 */
$field = MarcVariableField::fromText('111 23$aAAA$b$cCCC$d');
check($field->getSubfieldIndex($field->subfields[0]) === 0
    && $field->getSubfieldIndex($field->subfields[1]) === 1
    && $field->getSubfieldIndex($field->subfields[2]) === 2
    && $field->getSubfieldIndex($field->subfields[3]) === 3);
 
/*
 * MarcDataField->getIndicator1()
 * MarcDataField->getIndicator2()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB');
check($field->getIndicator1() === '2');
check($field->getIndicator2() === '3');

/*
 * MarcDataField->setIndicator1()
 * MarcDataField->setIndicator2()
 */
$field = new MarcDataField('111');
$field->setIndicator1('2');
check($field->ind1 === '2');
$field->setIndicator2('3');
check($field->ind2 === '3');

/*
 * MarcDataField->getSubfields()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
check(count($field->getSubfields()) === 3);
check(count($field->getSubfields('b')) === 1);
check(count($field->getSubfields('z')) === 0);
check(count($field->getSubfields(array('z', 'b', 'c'))) === 2);

$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check(count($field->getSubfields('1')) === 2);

/*
 * MarcDataField->getSubfield()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
check($field->getSubfield()->data === 'AAA');
check($field->getSubfield('b')->data === 'BBB');
check($field->getSubfield('z') === null);
check($field->getSubfield(array('z', 'b', 'c'))->data === 'BBB');

$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check($field->getSubfield('1')->isEmbeddedField());

/*
 * MarcDataField->getSubfieldData()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
check($field->getSubfieldData() === 'AAA');
check($field->getSubfieldData('b') === 'BBB');
check($field->getSubfieldData('z') === null);
check($field->getSubfieldData(array('z', 'b', 'c')) === 'BBB');

$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check($field->getSubfieldData('1')->isControlField());
    
/*
 * MarcDataField->getRegularSubfields()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
check(count($field->getRegularSubfields()) === 3);
check(count($field->getRegularSubfields(null, '/^BBB/u')) === 1);
check(count($field->getRegularSubfields(null, '/[CZ]/u')) === 1);
check(count($field->getRegularSubfields(null, '/^(BBB|CCC|ZZZ)$/u')) === 2);
check(count($field->getRegularSubfields(null, 'BBB')) === 1);
check(count($field->getRegularSubfields('b', '/^[BC]/u')) === 1);
check(count($field->getRegularSubfields(array('b', 'c'), '/^[C]/u')) === 1);
check(count($field->getRegularSubfields('c', 'CCC')) === 1);
check(count($field->getRegularSubfields('z')) === 0);
check(count($field->getRegularSubfields('1', '/[BCZ]/u')) === 0);

/*
 * MarcDataField->getRegularSubfield()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
check($field->getRegularSubfield()->data === 'AAA');
check($field->getRegularSubfield(null, '/^BBB/u')->data === 'BBB');
check($field->getRegularSubfield(null, '/[CZ]/u')->data === 'CCC');
check($field->getRegularSubfield(null, '/^(BBB|CCC|ZZZ)$/u')->data === 'BBB');
check($field->getRegularSubfield(null, 'BBB')->data === 'BBB');
check($field->getRegularSubfield('b', '/^[BC]/u')->data === 'BBB');
check($field->getRegularSubfield(array('b', 'c'), '/^[C]/u')->data === 'CCC');
check($field->getRegularSubfield('c', 'CCC')->data === 'CCC');
check($field->getRegularSubfield('z') === null);
check($field->getRegularSubfield('1', '/[BCZ]u/') === null);

/*
 * MarcDataField->getRegularSubfieldData()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
check($field->getRegularSubfieldData() === 'AAA');
check($field->getRegularSubfieldData(null, '/^BBB/u') === 'BBB');
check($field->getRegularSubfieldData(null, '/[CZ]/u') === 'CCC');
check($field->getRegularSubfieldData(null, '/^(BBB|CCC|ZZZ)$/u') === 'BBB');
check($field->getRegularSubfieldData(null, 'BBB') === 'BBB');
check($field->getRegularSubfieldData('b', '/^[BC]/u') === 'BBB');
check($field->getRegularSubfieldData(array('b', 'c'), '/^[C]/u') === 'CCC');
check($field->getRegularSubfieldData('c', 'CCC') === 'CCC');
check($field->getRegularSubfieldData('z') === null);
check($field->getRegularSubfieldData('1', '/[BCZ]/u') === null);

/*
 * MarcDataField->addSubfields()
 */
$field = new MarcDataField('111', '2', '3');
$field->addSubfields(array(new MarcSubfield('a', 'AAA'), new MarcSubfield('b', 'BBB')));
check(count($field->subfields) === 2 && $field->subfields[0]->data === 'AAA'
    && $field->subfields[1]->data === 'BBB');
$field->addSubfields(array(new MarcSubfield('c', 'CCC'), new MarcSubfield('d', 'DDD')));
check(count($field->subfields) === 4 && $field->subfields[2]->data === 'CCC'
    && $field->subfields[3]->data === 'DDD');

/*
 * MarcDataField->addSubfield()
 */
$field = new MarcDataField('111', '2', '3');
$field->addSubfield(new MarcSubfield('a', 'AAA'));
check(count($field->subfields) === 1 && $field->subfields[0]->data === 'AAA');
$field->addSubfield(new MarcSubfield('b', 'BBB'));
check(count($field->subfields) === 2 && $field->subfields[1]->data === 'BBB');
$field->addSubfield(new MarcSubfield('c', 'CCC'));
check(count($field->subfields) === 3 && $field->subfields[2]->data === 'CCC');

/*
 * MarcDataField->addNonEmptySubfield()
 */
$field = new MarcDataField('111', '2', '3');
$field->addNonEmptySubfield(new MarcSubfield('a', 'AAA'));
check(count($field->subfields) === 1 && $field->subfields[0]->data === 'AAA');
$field->addNonEmptySubfield(new MarcSubfield('b', ''));
check(count($field->subfields) === 1);
$field->addNonEmptySubfield(new MarcSubfield('c', null));
check(count($field->subfields) === 1);

/*
 * MarcDataField->insertSubfields()
 */
$field = new MarcDataField('111', '2', '3');
$field->insertSubfields(0, array(new MarcSubfield('a', 'A'), new MarcSubfield('b', 'B')));
$field->insertSubfields(0, array(new MarcSubfield('c', 'C'), new MarcSubfield('d', 'D')));
$field->insertSubfields(1, array(new MarcSubfield('e', 'E'), new MarcSubfield('f', 'F')));

try {
    $field->insertSubfields(-1, array(new MarcSubfield('g', 'G'), new MarcSubfield('h', 'H')));
    throw new \Exception('check failed');
} catch (MarcException $e) {
    if ($e->getMessage() !== 'invalid position specified') {
        throw $e;
    }
}
try {
    $field->insertSubfields($field->size() + 1, array(new MarcSubfield('g', 'G'), new MarcSubfield('h', 'H')));
    throw new \Exception('check failed');
} catch (MarcException $e) {
    if ($e->getMessage() !== 'invalid position specified') {
        throw $e;
    }
}

check(count($field->subfields) === 6
  && $field->subfields[0]->code === 'c'
  && $field->subfields[1]->code === 'e'
  && $field->subfields[2]->code === 'f'
  && $field->subfields[3]->code === 'd'
  && $field->subfields[4]->code === 'a'
  && $field->subfields[5]->code === 'b');

/*
 * MarcDataField->insertSubfield()
 */
$field = new MarcDataField('111', '2', '3');
$field->insertSubfield(0, new MarcSubfield('a', 'A'));
$field->insertSubfield(0, new MarcSubfield('b', 'B'));
$field->insertSubfield(1, new MarcSubfield('c', 'C'));

try {
    $field->insertSubfield(-1, new MarcSubfield('d', 'D'));
    throw new \Exception('check failed');
} catch (MarcException $e) {
    if ($e->getMessage() !== 'invalid position specified') {
        throw $e;
    }
}
try {
    $field->insertSubfield($field->size() + 1, new MarcSubfield('d', 'D'));
    throw new \Exception('check failed');
} catch (MarcException $e) {
    if ($e->getMessage() !== 'invalid position specified') {
        throw $e;
    }
}

check(count($field->subfields) === 3
    && $field->subfields[0]->code === 'b'
    && $field->subfields[1]->code === 'c'
    && $field->subfields[2]->code === 'a');

/*
 * MarcDataField->removeSubfields()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
$field->removeSubfields($field->getSubfields(array('a', 'c')));
check(count($field->subfields) === 1
  && count($field->getSubfields(array('a', 'c'))) === 0);

$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
$field->removeSubfields(array(1));
check(count($field->subfields) === 2 && $field->getSubfield('b') === null);

$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
$field->removeSubfields($field->getSubfields('b'));
check(count($field->subfields) === 2 && $field->getSubfield('b') === null);

$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
$field->removeSubfields($field->getSubfields('1'));
check(count($field->subfields) === 0);

/*
 * MarcDataField->removeSubfield()
 */
$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
$field->removeSubfield($field->getSubfield('a'));
check(count($field->subfields) === 2 && $field->getSubfield('a') === null);

$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
$field->removeSubfield(1);
check(count($field->subfields) === 2 && $field->getSubfield('b') === null);

$field = MarcVariableField::fromText('111 23$aAAA$bBBB$cCCC');
$field->removeSubfields($field->getSubfields('b'));
check(count($field->subfields) === 2 && $field->getSubfield('b') === null);

$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
$field->removeSubfields($field->getSubfields('1'));
check(count($field->subfields) === 0);

/*
 * MarcDataField->getVariableFields()
 */
$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check(count($field->getVariableFields()) === 2);
check(count($field->getVariableFields('001')) === 1);
check(count($field->getVariableFields('/1../u')) === 1);
check(count($field->getVariableFields(array('001', '100'))) === 2);
check(count($field->getVariableFields(array('001', '/1../u'))) === 2);
check(count($field->getVariableFields('005')) === 0);

/*
 * MarcDataField->getVariableField()
 */
$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check($field->getVariableField()->tag === '001');
check($field->getVariableField('001')->tag === '001');
check($field->getVariableField('/1../u')->tag === '100');
check($field->getVariableField(array('001', '100'))->tag === '001');
check($field->getVariableField(array('001', '/1../u'))->tag === '001');
check($field->getVariableField('005') === null);

/*
 * MarcDataField->getControlFieldData()
 */
$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check($field->getControlFieldData('001') === 'ID2');
check($field->getControlFieldData('/0../u') === 'ID2');
check($field->getControlFieldData(array('005', '/0../u')) === 'ID2');
check($field->getControlFieldData('005') === null);

/*
 * MarcDataField->getControlNumberField()
 */
$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check($field->getControlNumberField()->data === 'ID2');
$field = MarcVariableField::fromText('444 56$110023$xXXX$yYYY');
check($field->getControlNumberField() === null);

/*
 * MarcDataField->getControlNumber()
 */
$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
check($field->getControlNumber() === 'ID2');
$field = MarcVariableField::fromText('444 56$110023$xXXX$yYYY');
check($field->getControlNumber() === null);

/*
 * MarcDataField->addVariableField()
 */
$field = new MarcDataField('111', '2', '3');
$field->addVariableField(new MarcControlField('001', 'ID2'));
check(count($field->subfields) === 1 && $field->subfields[0]->data->tag === '001');
$field->addVariableField(0, new MarcDataField('100', '2', '3'));
check(count($field->subfields) === 2 && $field->subfields[0]->data->tag === '100');

/*
 * MarcDataField->removeVariableFields()
 */
$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
$field->removeVariableFields($field->getVariableFields('100'));
check(count($field->subfields) === 1 && $field->getVariableField('100') === null);

$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
$field->removeVariableFields(array(0));
check(count($field->subfields) === 1 && $field->getVariableField('001') === null);

/*
 * MarcDataField->removeVariableField()
 */
$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
$field->removeVariableField($field->getVariableField('100'));
check(count($field->subfields) === 1 && $field->getVariableField('100') === null);

$field = MarcVariableField::fromText('444 56$1001ID2$110023$xXXX$yYYY');
$field->removeVariableField(0);
check(count($field->subfields) === 1 && $field->getVariableField('001') === null);

echo 'OK' . PHP_EOL;
?>
