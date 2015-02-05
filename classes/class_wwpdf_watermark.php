<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WWPDFWatermark' ) ) :

	class WWPDFWatermark {

		public function __construct($origfile, $newfile, $wmtext2) {

			$this->pdf = new FPDI_Protection();
			$this->file = $origfile;
			$this->newfile = $newfile;
			$this->wmtext2 = $wmtext2; 

		}

		public static function apply_and_spit($origfile, $newfile, $wmtext2) {

			$wm = new WWPDFWatermark($origfile, $newfile, $wmtext2);
			if($wm->is_watermarked()) {
				return $wm->spit_watermarked();
			} else {
				$wm->do_watermark();
				return $wm->spit_watermarked();
			}
		
		}

		public function do_watermark() {

			$pagecount = $this->pdf->setSourceFile($this->file);

			$wwpdf_footer_y = get_option( 'wwpdf_footer_y' );

			$wwpdf_font = get_option( 'wwpdf_font' );			
	
			$wwpdf_footer_size = get_option( 'wwpdf_footer_size' );
			$this->pdf->SetFont( $wwpdf_font, '', $wwpdf_footer_size );	

			$wwpdf_footer_color = $this->hex2rgb( get_option( 'wwpdf_footer_color' ) );
			$rgb_array = explode(",", $wwpdf_footer_color);
			$this->pdf->SetTextColor($rgb_array[0],$rgb_array[1],$rgb_array[2]);

			for( $i = 1; $i <= $pagecount; $i++ ) {
				$tplidx = $this->pdf->importPage($i);
				$specs = $this->pdf->getTemplateSize($tplidx);
				$this->pdf->addPage($specs['h'] > $specs['w'] ? 'P' : 'L');
							
				$this->pdf->Text( ($specs['w'] / 2) - ($this->pdf->GetStringWidth($this->wmtext2) / 2), $wwpdf_footer_y, $this->wmtext2);

				$this->pdf->useTemplate($tplidx, null, null, $specs['w'], $specs['h'], true);
				
			}

			$this->pdf->Output($this->newfile, 'F');

		} // end function do_watermark

		public function is_watermarked() {
			return (file_exists($this->newfile));
		}

		public function spit_watermarked() {
			return $this->newfile;
		}

		protected function hex2rgb($hex) {
			$hex = str_replace("#", "", $hex);
			$r = hexdec(substr($hex,0,2));
			$g = hexdec(substr($hex,2,2));
			$b = hexdec(substr($hex,4,2));
			$rgb = array($r, $g, $b);
			return implode(",", $rgb);
		}
	
		protected function _rotate($angle,$x=-1,$y=-1) {
			if($x==-1)
				$x=$this->pdf->x;
			if($y==-1)
				$y=$this->pdf->y;
			if($this->pdf->angle!=0)
				$this->pdf->_out('Q');
				$this->pdf->angle=$angle;
			if($angle!=0) {
				$angle*=M_PI/180;
				$c=cos($angle);
					$s=sin($angle);
				$cx=$x*$this->pdf->k;
					$cy=($this->pdf->h-$y)*$this->pdf->k;
				$this->pdf->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
				}
		} // end function _rotate

	} // end Class WWPDFWatermark
	
endif;

?>