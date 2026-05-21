<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/efpic/emails/email-footer.php. 
 *
 * Please note: On occasion efpic will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://efpic.io/docs/template-structure/
 * @version 1.7.0
 */

defined( 'ABSPATH' ) || exit;
?>
				</div>
			</td>
		</tr>
	</table>
	<table class="footer-wrap">
		<tr>
			<td class="container">
				<div class="content">
					<table>
						<tr>
							<td align="center">
								<?php if ( $args['efpic_logo_uri'] ) {
									echo '<p>powered by</p><a href="https://efpic.io/"><img src="' . $args['efpic_logo_uri'] . '" width="80" style="width: 80px; height: auto;" alt="efpic Logo"></a>';
								} ?>
							</td>
						</tr>
					</table>
				</div>	
			</td>
		</tr>
	</table>
</body>
</html>