<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\html2pdf\converters;

use Yii;
use yii\helpers\ArrayHelper;
use yii2tech\html2pdf\BaseConverter;

/**
 * Mpdf converts file using [mpdf](https://github.com/mpdf/mpdf) library.
 *
 * This converter requires `mpdf` library to be installed. This can be done via composer:
 *
 * ```
 * composer require --prefer-dist "mpdf/mpdf:^6.0.0|^7.0.0"
 * ```
 *
 * @see http://mpdf.github.io
 * @see https://github.com/mpdf/mpdf
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Mpdf extends BaseConverter
{
    /**
     * {@inheritdoc}
     */
    protected function convertInternal($html, $outputFileName, $options)
    {
        $charset = ArrayHelper::remove($options, 'charset', Yii::$app->charset);
        $pageSize = ArrayHelper::remove($options, 'pageSize', 'A4');

        if (class_exists('Mpdf\Mpdf')) {
            $config = [
                'mode' => $charset,
                'format' => $pageSize,
                'tempDir' => Yii::getAlias(ArrayHelper::remove($options, 'tempDir', '@runtime')),
            ];

            $pdf = new \Mpdf\Mpdf($config);

            if (isset($options['fontDir'])) {
                $pdf->AddFontDirectory(Yii::getAlias($options['fontDir']));
                unset($options['fontDir']);
            }

        } else {
            $pdf = new \mPDF($charset, $pageSize);
        }

        foreach ($options as $name => $value) {
            $setter = 'Set' . $name;
            if (method_exists($pdf, $setter)) {
                if (is_array($value)) {
                    call_user_func_array([$pdf, $setter], $value);
                } else {
                    $pdf->$setter($value);
                }
            } else {
                $pdf->$name = $value;
            }
        }

        $pdf->WriteHTML($html);
        $pdf->Output($outputFileName, 'F');
    }
}