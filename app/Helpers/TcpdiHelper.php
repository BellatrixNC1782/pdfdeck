<?php

namespace App\Helpers;

use setasign\Fpdi\Tcpdf\Fpdi;

/**
 * Small wrapper around FPDI‑TCPDF that exposes a
 * setPageRotationCompat() helper for older/newer TCPDF versions.
 */
class TcpdiHelper extends Fpdi
{
    public function __construct()
    {
        parent::__construct();
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(false, 0);

        // optional ‑ switch off default header/footer lines
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
    }

    /**
     * Add or overwrite the /Rotate entry for the *current* page.
     *
     * @param int $angle 0, 90, 180 or 270
     */
    public function setPageRotationCompat(int $angle): void
    {
        $angle = ($angle % 360 + 360) % 360;   // normalise

        if ($angle === 0 || $this->page < 1) {
            return;                            // nothing to do
        }

        // TCPDF stores the page boxes in $this->pagedim[$pageNo]
        $this->pagedim[$this->page]['Rotate'] = $angle;
    }
}
