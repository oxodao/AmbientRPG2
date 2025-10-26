import { Tooltip, TooltipContent, TooltipTrigger } from '@radix-ui/react-tooltip';
import type { VariantProps } from 'class-variance-authority';
import { useTranslation } from 'react-i18next';
import { Button, type buttonVariants } from './button';

type ButtonProps = React.ComponentProps<'button'> &
	VariantProps<typeof buttonVariants> & {
		asChild?: boolean;
	};

function IconButton({
	tooltipTranslationKey,
	tooltipTranslationOptions,
	...props
}: ButtonProps & { tooltipTranslationKey: string; tooltipTranslationOptions?: Record<string, unknown> }) {
	const { t } = useTranslation();

	const text = t(tooltipTranslationKey, tooltipTranslationOptions);

	return (
		<Tooltip>
			<TooltipTrigger>
				<Button {...props} aria-description={text} />
			</TooltipTrigger>

			<TooltipContent>{text}</TooltipContent>
		</Tooltip>
	);
}

export { IconButton };
