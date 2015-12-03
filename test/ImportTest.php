<?php
/**
 * ImportTest.php
 *
 * @since       2011-05-23
 * @category    Library
 * @package     PdfFont
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2011-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf-font
 *
 * This file is part of tc-lib-pdf-font software library.
 */

namespace Test;

/**
 * Import Test
 *
 * @since       2011-05-23
 * @category    Library
 * @package     PdfFont
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2011-2015 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf-font
 */
class ImportTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        //$this->markTestSkipped(); // skip this test
    }

    public function testImportEmptyName()
    {
        $this->setExpectedException('\Com\Tecnick\Pdf\Font\Exception');
        new \Com\Tecnick\Pdf\Font\Import('');
    }

    public function testImportExist()
    {
        $fin = __DIR__.'/../util/vendor/font/core/Helvetica.afm';
        $outdir = __DIR__.'/../target/tmptest/';
        system('rm -rf '.$outdir.' && mkdir -p '.$outdir);
        new \Com\Tecnick\Pdf\Font\Import($fin, $outdir);
        $this->setExpectedException('\Com\Tecnick\Pdf\Font\Exception');
        new \Com\Tecnick\Pdf\Font\Import($fin, $outdir);
    }

    public function testImportWrongFile()
    {
        $this->setExpectedException('\Com\Tecnick\Pdf\Font\Exception');
        new \Com\Tecnick\Pdf\Font\Import(__DIR__.'/../util/vendor/font/core/Missing.afm');
    }

    public function testImportDefaultOutput()
    {
        define('K_PATH_FONTS', __DIR__.'/../target/tmptest/');
        $this->setExpectedException('\Com\Tecnick\Pdf\Font\Exception');
        new \Com\Tecnick\Pdf\Font\Import(__DIR__.'/../util/vendor/font/core/Missing.afm');
    }

    public function testImportUnsupportedType()
    {
        $fin = __DIR__.'/../util/vendor/font/core/Helvetica.afm';
        $outdir = __DIR__.'/../target/tmptest/core/';
        system('rm -rf '.$outdir.' && mkdir -p '.$outdir);
        $this->setExpectedException('\Com\Tecnick\Pdf\Font\Exception');
        new \Com\Tecnick\Pdf\Font\Import($fin, $outdir, 'ERROR');
    }

    public function testImportUnsupportedOpenType()
    {
        $outdir = __DIR__.'/../target/tmptest/core/';
        system('rm -rf '.$outdir.' && mkdir -p '.$outdir);
        file_put_contents($outdir.'test.ttf', 'OTTO 1234');
        $this->setExpectedException('\Com\Tecnick\Pdf\Font\Exception');
        new \Com\Tecnick\Pdf\Font\Import($outdir.'test.ttf', $outdir);
    }

    /**
     * @dataProvider importDataProvider
     */
    public function testImport($fontdir, $font, $outname, $type = null, $encoding = null)
    {
        $indir = __DIR__.'/../util/vendor/font/'.$fontdir.'/';
        $outdir = __DIR__.'/../target/tmptest/'.$fontdir.'/';
        system('rm -rf '.__DIR__.'/../target/tmptest/ && mkdir -p '.$outdir);
        
        $imp = new \Com\Tecnick\Pdf\Font\Import($indir.$font, $outdir, $type, $encoding);
        $this->assertEquals($outname, $imp->getFontName());

        $metric = $imp->getFontMetrics();

        $json = json_decode(file_get_contents($outdir.$outname.'.json'), true);
        $this->assertNotNull($json);

        $this->assertArrayHasKey('type', $json);
        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('up', $json);
        $this->assertArrayHasKey('ut', $json);
        $this->assertArrayHasKey('dw', $json);
        $this->assertArrayHasKey('diff', $json);
        $this->assertArrayHasKey('desc', $json);
        $this->assertArrayHasKey('Flags', $json['desc']);
        
        $this->assertEquals('['.$metric['bbox'].']', $json['desc']['FontBBox']);
        $this->assertEquals($metric['italicAngle'], $json['desc']['ItalicAngle']);
        $this->assertEquals($metric['Ascent'], $json['desc']['Ascent']);
        $this->assertEquals($metric['Descent'], $json['desc']['Descent']);
        $this->assertEquals($metric['Leading'], $json['desc']['Leading']);
        $this->assertEquals($metric['CapHeight'], $json['desc']['CapHeight']);
        $this->assertEquals($metric['XHeight'], $json['desc']['XHeight']);
        $this->assertEquals($metric['StemV'], $json['desc']['StemV']);
        $this->assertEquals($metric['StemH'], $json['desc']['StemH']);
        $this->assertEquals($metric['AvgWidth'], $json['desc']['AvgWidth']);
        $this->assertEquals($metric['MaxWidth'], $json['desc']['MaxWidth']);
        $this->assertEquals($metric['MissingWidth'], $json['desc']['MissingWidth']);
    }

    public function importDataProvider()
    {
        return array(
            array('core', 'Courier.afm', 'courier'),
            array('core', 'Courier-Bold.afm', 'courierb'),
            array('core', 'Courier-BoldOblique.afm', 'courierbi'),
            array('core', 'Courier-Oblique.afm', 'courieri'),
            array('core', 'Helvetica.afm', 'helvetica'),
            array('core', 'Helvetica-Bold.afm', 'helveticab'),
            array('core', 'Helvetica-BoldOblique.afm', 'helveticabi'),
            array('core', 'Helvetica-Oblique.afm', 'helveticai'),
            array('core', 'Symbol.afm', 'symbol'),
            array('core', 'Times.afm', 'times'),
            array('core', 'Times-Bold.afm', 'timesb'),
            array('core', 'Times-BoldItalic.afm', 'timesbi'),
            array('core', 'Times-Italic.afm', 'timesi'),
            array('core', 'ZapfDingbats.afm', 'zapfdingbats'),

            array('pdfa/pfb', 'PDFACourierBoldOblique.pfb', 'pdfacourierbi', null, null),
            array('pdfa/pfb', 'PDFACourierBold.pfb', 'pdfacourierb', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFACourierOblique.pfb', 'pdfacourieri', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFACourier.pfb', 'pdfacourier', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFAHelveticaBoldOblique.pfb', 'pdfahelveticabi', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFAHelveticaBold.pfb', 'pdfahelveticab', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFAHelveticaOblique.pfb', 'pdfahelveticai', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFAHelvetica.pfb', 'pdfahelvetica', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFASymbol.pfb', 'pdfasymbol', '', 'symbol'),
            array('pdfa/pfb', 'PDFATimesBoldItalic.pfb', 'pdfatimesbi', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFATimesBold.pfb', 'pdfatimesb', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFATimesItalic.pfb', 'pdfatimesi', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFATimes.pfb', 'pdfatimes', 'Type1', 'cp1252'),
            array('pdfa/pfb', 'PDFAZapfDingbats.pfb', 'pdfazapfdingbats'),

            array('freefont', 'FreeMonoBoldOblique.ttf', 'freemonobi'),
            array('freefont', 'FreeMonoBold.ttf', 'freemonob'),
            array('freefont', 'FreeMonoOblique.ttf', 'freemonoi'),
            array('freefont', 'FreeMono.ttf', 'freemono'),
            array('freefont', 'FreeSansBoldOblique.ttf', 'freesansbi'),
            array('freefont', 'FreeSansBold.ttf', 'freesansb'),
            array('freefont', 'FreeSansOblique.ttf', 'freesansi'),
            array('freefont', 'FreeSans.ttf', 'freesans'),
            array('freefont', 'FreeSerifBoldItalic.ttf', 'freeserifbi'),
            array('freefont', 'FreeSerifBold.ttf', 'freeserifb'),
            array('freefont', 'FreeSerifItalic.ttf', 'freeserifi'),
            array('freefont', 'FreeSerif.ttf', 'freeserif'),

            array('unifont', 'unifont.ttf', 'unifont'),

            array('cid0', 'cid0cs.ttf', 'cid0cs', 'CID0CS'),
            array('cid0', 'cid0ct.ttf', 'cid0ct', 'CID0CT'),
            array('cid0', 'cid0jp.ttf', 'cid0jp', 'CID0JP'),
            array('cid0', 'cid0kr.ttf', 'cid0kr', 'CID0KR'),
        );
    }
}
