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
 * MarcSubfield constructor.
 */
$subfield = new MarcSubfield();
check($subfield->code === '?' && $subfield->data === '');

$subfield = new MarcSubfield('a', 'AAA');
check($subfield->code === 'a' && $subfield->data === 'AAA');

$subfield1 = new MarcSubfield('a', 'AAA');
$subfield2 = clone $subfield1;
check($subfield1 !== $subfield2 && $subfield1->equalsTo($subfield2));

$subfield1 = MarcSubfield::fromText('$1001ID2');
$subfield2 = clone $subfield1;
check($subfield2->data->isControlField());

/*
 * MarcSubfield->assign()
 */
$subfield1 = new MarcSubfield('a', 'AAA');
$subfield2 = new MarcSubfield('a', 'AAA2');
$subfield1->assign($subfield2);
check($subfield1 !== $subfield2 && $subfield1->equalsTo($subfield2));

/*
 * MarcSubfield->equalsTo()
 */
$subfield1 = new MarcSubfield('a', 'AAA');
$subfield2 = new MarcSubfield('a', 'AAA2');
$subfield3 = new MarcSubfield('a', 'aaa');
$subfield4 = new MarcSubfield('1', new MarcControlField('001', 'ID1'));
$subfield5 = new MarcSubfield('1', new MarcControlField('001', 'id2'));
$subfield6 = MarcSubfield::fromText('$110023$xXXX$yYYY');
$subfield7 = MarcSubfield::fromText('$110023$yYYY$xXXX');

check($subfield1->equalsTo($subfield1));
check(!$subfield1->equalsTo($subfield2));
check($subfield1->equalsTo($subfield2, array('ignoreChars' => '/[0-9]/u')));
check(!$subfield1->equalsTo($subfield3));
check($subfield1->equalsTo($subfield3, array('ignoreCase' => true)));
check(!$subfield4->equalsTo($subfield5));
check($subfield4->equalsTo($subfield5, array('ignoreCase' => true, 'ignoreChars' => '/[0-9]/u')));
check(!$subfield6->equalsTo($subfield7));
check($subfield6->equalsTo($subfield7, array('ignoreOrder' => true)));

/*
 * MarcSubfield->isEmpty()
 */
check(MarcSubfield::fromText('$a')->isEmpty());
check(!MarcSubfield::fromText('$aAAA')->isEmpty());
check(!MarcSubfield::fromText('$1001ID1')->isEmpty());
check(!MarcSubfield::fromText('$110023$xXXX$yYYY')->isEmpty());

/*
 * MarcSubfield->isEmbeddedField()
 */
check(!MarcSubfield::fromText('$aAAA')->isEmbeddedField());
check(MarcSubfield::fromText('$1001ID1')->isEmbeddedField());
check(MarcSubfield::fromText('$110023$xXXX$yYYY')->isEmbeddedField());

/*
 * MarcSubfield->getCode()
 */
$subfield = MarcSubfield::fromText('$aAAA');
check($subfield->getCode() === 'a');

/*
 * MarcSubfield->setCode()
 */
$subfield = new MarcSubfield();
$subfield->setCode('b');
check($subfield->code === 'b');

/*
 * MarcSubfield->getData()
 */
$subfield = MarcSubfield::fromText('$aAAA');
check($subfield->getData() === 'AAA');
$subfield = MarcSubfield::fromText('$110023$xXXX$yYYY');
check($subfield->getData()->isDataField());

/*
 * MarcSubfield->setData()
 */
$subfield = new MarcSubfield('b');
$subfield->setData('text');
check($subfield->data === 'text');

echo 'OK' . PHP_EOL;
?>
