import type { FormHTMLAttributes, ReactNode } from 'react';
import {
	type FieldValues,
	FormProvider,
	type SubmitErrorHandler,
	type SubmitHandler,
	type UseFormReturn,
} from 'react-hook-form';
import { useTranslation } from 'react-i18next';
import { ValidationError } from '@/api/violations_error';

type ControlledFormProps<TFieldValues extends FieldValues> = {
	methods: UseFormReturn<TFieldValues>;
	onSubmit: SubmitHandler<TFieldValues>;
	onError?: SubmitErrorHandler<TFieldValues>;
	children: ReactNode;
	fieldsetClassname?: string;
	actionButtons: ReactNode;
	globallyDisabled?: boolean;
} & Omit<FormHTMLAttributes<HTMLFormElement>, 'onSubmit' | 'children'>;

export default function Form<TFieldValues extends FieldValues = FieldValues>({
	methods,
	onSubmit,
	onError,
	children,
	className,
	fieldsetClassname,
	actionButtons,
	globallyDisabled,
	...formProps
}: ControlledFormProps<TFieldValues>) {
	const { t } = useTranslation();
	const {
		setError,
		formState: { isSubmitting },
	} = methods;

	const handleFormSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
		e.preventDefault();

		const isValid = await methods.trigger();
		if (!isValid) {
			return;
		}

		try {
			methods.clearErrors('root');
			await onSubmit(methods.getValues(), e);
		} catch (error) {
			if (error instanceof ValidationError) {
				const globalErrors = error.applyToReactHookForm(setError);
				if (globalErrors.length > 0) {
					setError('root', {
						type: 'server',
						message: globalErrors.join('\n'),
					});
				}
				return;
			}

			setError('root', {
				type: 'value',
				message: (error as Error).message ?? t('errors.generic.title'),
			});

			onError?.(error as any, e);
		}
	};

	return (
		<FormProvider {...methods}>
			<form noValidate className={`flex flex-col gap-2 ${className}`} {...formProps} onSubmit={handleFormSubmit}>
				<fieldset disabled={isSubmitting || globallyDisabled} className={fieldsetClassname ?? ''}>
					{children}
				</fieldset>

				{methods.formState.errors.root?.message && methods.formState.errors.root?.message.length > 0 && (
					<p className='mt-2 text-sm text-red-500 text-center' role='alert'>
						{methods.formState.errors.root.message}
					</p>
				)}

				{actionButtons}
			</form>
		</FormProvider>
	);
}
