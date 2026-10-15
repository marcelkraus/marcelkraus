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
 * Renders the printed curriculum vitae; nothing outside this class knows mPDF.
 * The geometry and the fonts are in docs/CURRICULUM_VITAE.md.
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
        $document->SetHTMLHeader(sprintf(
            '<div style="text-align: right;"><img src="%s" style="width: %s; height: %s;"></div>',
            $this->projectDir . '/public/images/logo.svg',
            self::LOGO_WIDTH_IN_MILLIMETRES,
            self::LOGO_HEIGHT_IN_MILLIMETRES,
        ));
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
            // Centered in the bottom margin at 287 mm; measured, hence 8.
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
            // Off: mPDF would rescale single tables and mix body sizes on one page.
            'shrink_tables_to_fit' => 0,
            'tempDir' => $this->projectDir . '/var/mpdf',
        ]);
    }
}
