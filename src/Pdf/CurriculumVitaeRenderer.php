<?php

declare(strict_types=1);

namespace App\Pdf;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

/**
 * Renders the printed curriculum vitae. The controller hands over the content
 * and receives bytes; nothing outside this class knows that mPDF exists.
 *
 * The page geometry is the measured stationery, not a guess: the logo sits at
 * 20 mm from the top edge and is 15 mm tall, so the text block starts at
 * 55 mm – on every page, because the header repeats itself. Left and right
 * are 20 mm, and the bottom is 20 mm as well. The stationery only fixes the
 * top and the sides; the foot is free, and ten millimetres more paper per page
 * is what keeps this document at two.
 *
 * Two fonts are embedded. Aller is already a static TrueType file and comes
 * straight from the web font directory. JetBrains Mono is only published as a
 * variable woff2, which mPDF can neither read nor subset, so `assets/fonts`
 * carries two static instances derived from it – see `assets/README.md`.
 */
final class CurriculumVitaeRenderer
{
    private const MARGIN_TOP_IN_MILLIMETRES = 55;
    private const MARGIN_HEADER_IN_MILLIMETRES = 20;
    private const MARGIN_SIDE_IN_MILLIMETRES = 20;
    private const MARGIN_BOTTOM_IN_MILLIMETRES = 20;

    private const LOGO_WIDTH_IN_MILLIMETRES = '61.03mm';
    private const LOGO_HEIGHT_IN_MILLIMETRES = '15mm';

    public function __construct(
        private readonly Environment $twig,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    /**
     * @param array<string, mixed> $context
     */
    public function render(array $context): string
    {
        $document = $this->createDocument();

        $document->SetTitle('Lebenslauf Marcel Kraus');
        $document->SetAuthor('Marcel Kraus');
        $document->SetCreator('marcelkraus.de');

        // The lockup lives in the page header, which is what makes it repeat
        // without the body having to know about it.
        $document->SetHTMLHeader(sprintf(
            '<div style="text-align: right;"><img src="%s" style="width: %s; height: %s;"></div>',
            $this->projectDir . '/public/images/logo.svg',
            self::LOGO_WIDTH_IN_MILLIMETRES,
            self::LOGO_HEIGHT_IN_MILLIMETRES,
        ));

        // A quiet page number, centred, in the mono voice the technical
        // layer uses everywhere else. Two pages do not need it to find their
        // way back together – but a sheet that falls out of a folder does.
        $document->SetHTMLFooter(
            '<div style="text-align: center; font-family: jetbrainsmono; font-size: 8pt;'
            . ' color: #737373;">&#8211; {PAGENO} &#8211;</div>'
        );

        $document->WriteHTML($this->twig->render('pdf/curriculum-vitae.html.twig', $context));

        return (string) $document->Output('', Destination::STRING_RETURN);
    }

    private function createDocument(): Mpdf
    {
        $directories = (new ConfigVariables())->getDefaults()['fontDir'];
        $fonts = (new FontVariables())->getDefaults()['fontdata'];

        return new Mpdf([
            'format' => 'A4',
            'margin_top' => self::MARGIN_TOP_IN_MILLIMETRES,
            'margin_header' => self::MARGIN_HEADER_IN_MILLIMETRES,
            'margin_left' => self::MARGIN_SIDE_IN_MILLIMETRES,
            'margin_right' => self::MARGIN_SIDE_IN_MILLIMETRES,
            'margin_bottom' => self::MARGIN_BOTTOM_IN_MILLIMETRES,
            // The page number sits in the middle of the bottom margin: the
            // band runs 277 to 297 mm, so its centre is 287 mm. Measured at
            // 285.0 mm with a footer margin of 10, hence 8.
            'margin_footer' => 8,
            'fontDir' => array_merge($directories, [
                $this->projectDir . '/public/fonts',
                $this->projectDir . '/assets/fonts',
            ]),
            'fontdata' => $fonts + [
                'aller' => ['R' => 'aller_regular.ttf', 'B' => 'aller_bold.ttf'],
                'jetbrainsmono' => [
                    'R' => 'jetbrains-mono-regular.ttf',
                    'B' => 'jetbrains-mono-bold.ttf',
                ],
            ],
            'default_font' => 'aller',
            'default_font_size' => 9,
            // mPDF shrinks a table whose content it believes will not fit,
            // silently and per table. That produced two different body sizes
            // on one page – the long stations came out smaller than the short
            // ones. Every column here has an explicit width, so nothing needs
            // rescuing and the type stays the size it was set at.
            'shrink_tables_to_fit' => 0,
            'tempDir' => $this->projectDir . '/var/mpdf',
        ]);
    }
}
