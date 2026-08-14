<?php
/**
 * Phone + OTP login/register form.
 *
 * Layout differs deliberately by breakpoint (per the two separate
 * exports): desktop is a true split screen (decorative image as its own
 * column); mobile stacks everything in one column with the same image
 * placed inline between the input and the submit button. Both reuse the
 * same Theme Options image/text fields — nothing is duplicated content,
 * only the image tag itself appears twice (once per breakpoint) since a
 * full-height side column and an inline block can't share one <img> tag.
 *
 * @package Negarin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$image        = negarin_option( 'login_image' );
$welcome_text = negarin_option( 'login_welcome_text', __( "به خانه نگارین\nخوش آمدید", 'negarin' ) );
?>
<div class="negarin-login grid grid-cols-1 md:grid-cols-2 md:min-h-[80vh]">
	<div class="flex flex-col justify-center px-6 md:px-16 py-10 md:py-16" x-data="negarinOtp()">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="block mb-8 md:mb-10 text-center md:text-right">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="font-serif tracking-[0.35em] text-3xl"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<template x-if="step === 'phone'">
			<div>
				<label class="block text-sm mb-3" for="negarin-phone"><?php esc_html_e( 'لطفا شماره موبایل خود را وارد نمایید', 'negarin' ); ?></label>
				<input id="negarin-phone" type="tel" inputmode="numeric" placeholder="<?php esc_attr_e( 'اینجا وارد نمایید', 'negarin' ); ?>"
					class="w-full border border-black/15 rounded-sm px-4 py-3 mb-6" dir="ltr"
					x-model="phone" @keyup.enter="requestCode()">

				<?php if ( $image ) : ?>
					<div class="md:hidden mb-6 -mx-6">
						<?php negarin_image( $image, 'negarin-grid-2', 'w-full h-48 object-cover' ); ?>
					</div>
				<?php endif; ?>

				<p class="text-red-600 text-sm mb-4" x-show="error" x-text="error"></p>

				<button class="btn btn--solid w-full" @click="requestCode()" :disabled="loading">
					<?php esc_html_e( 'ورود', 'negarin' ); ?>
				</button>
			</div>
		</template>

		<template x-if="step === 'code'">
			<div>
				<label class="block text-sm mb-3">
					<?php esc_html_e( 'کد تایید ارسال شده به شماره', 'negarin' ); ?>
					<span x-text="phone" dir="ltr" class="inline-block"></span>
					<?php esc_html_e( 'را وارد نمایید', 'negarin' ); ?>
				</label>
				<input id="negarin-code" type="tel" inputmode="numeric" maxlength="5" placeholder="<?php esc_attr_e( 'اینجا وارد نمایید', 'negarin' ); ?>"
					class="w-full border border-negarin-line rounded-0 px-4.5 py-4.5 mb-3 text-center tracking-[0.5em]" dir="ltr"
					x-model="code" @keyup.enter="verifyCode()">

				<div class="flex items-center justify-between text-sm mb-6">

					<button type="button" class="opacity-90 disabled:opacity-80 text-negarin-ink" @click="resendCode()" :disabled="cooldown > 0">
						<span x-show="cooldown > 0">
							<?php esc_html_e( 'تا ارسال مجدد:', 'negarin' ); ?>
							<span x-text="cooldown" class="text-negarin-red"></span>
						</span>
						<span x-show="cooldown === 0"><?php esc_html_e( 'ارسال مجدد کد', 'negarin' ); ?></span>
					</button>
                    <button type="button" class="text-negarin-red" @click="back()">
                        <?php esc_html_e( 'ویرایش شماره', 'negarin' ); ?>
                    </button>
				</div>

				<?php if ( $image ) : ?>
					<div class="md:hidden mb-6 -mx-6">
						<?php negarin_image( $image, 'negarin-grid-2', 'w-full h-48 object-cover' ); ?>
					</div>
				<?php endif; ?>

				<p class="text-negarin-red text-sm mb-2" x-show="error" x-text="error"></p>

				<button class="btn btn--solid w-full" @click="verifyCode()" :disabled="loading">
					<?php esc_html_e( 'ورود', 'negarin' ); ?>
				</button>
			</div>
		</template>

	</div>

    <div class="relative hidden md:block">
        <?php if ( $image ) : ?>
            <?php negarin_image( $image, 'negarin-hero', 'w-full h-full object-cover', false ); ?>
        <?php endif; ?>
    </div>
</div>
