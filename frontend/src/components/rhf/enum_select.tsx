import { type FieldError, type FieldPath, type FieldValues, useController, useFormContext } from 'react-hook-form';
import EnumSelect from '../generic/enum_select';
import { FieldGroup, FieldLabel, FieldError as ShadFieldError } from '../ui/field';

type RhfEnumSelectProps<TFieldValues extends FieldValues = FieldValues> = {
	name: FieldPath<TFieldValues>;
	enumName: string;
	label?: string;
	disabled?: boolean;
	className?: string;
};

export default function RhfEnumSelect<TFieldValues extends FieldValues = FieldValues>({
	name,
	enumName,
	label,
	disabled,
	className,
}: RhfEnumSelectProps<TFieldValues>) {
	const { control, formState } = useFormContext<TFieldValues>();
	if (!control) {
		throw new Error('RhfEnumSelect must be used inside a <FormProvider>.');
	}

	const {
		field,
		fieldState: { error },
	} = useController({ name, control });

	const errorForInput: FieldError | string | undefined = error;

	return (
		<FieldGroup className='w-full flex flex-col gap-1'>
			{label && <FieldLabel htmlFor={name as string}>{label}:</FieldLabel>}

			<EnumSelect
				id={name as string}
				enumName={enumName}
				value={field.value}
				onChange={field.onChange}
				disabled={disabled ?? formState.isSubmitting}
				className={className}
				aria-invalid={!!error || undefined}
				aria-describedby={errorForInput ? `${name}-error` : undefined}
			/>

			{errorForInput && (
				<ShadFieldError id={`${name}-error`}>
					{errorForInput && (typeof errorForInput === 'string' ? errorForInput : errorForInput.message)}
				</ShadFieldError>
			)}
		</FieldGroup>
	);
}
