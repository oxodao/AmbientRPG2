import { Eye, EyeOff } from 'lucide-react';
import { type InputHTMLAttributes, type ReactNode, useState } from 'react';
import {
	type FieldError,
	type FieldPath,
	type FieldValues,
	type RegisterOptions,
	useController,
	useFormContext,
} from 'react-hook-form';
import { FieldGroup, FieldLabel, FieldError as ShadFieldError } from '../ui/field';
import { Input } from '../ui/input';
import { InputGroup, InputGroupAddon, InputGroupButton, InputGroupInput } from '../ui/input-group';

type RhfInputProps<TFieldValues extends FieldValues = FieldValues> = {
	name: FieldPath<TFieldValues>;
	label?: string;
	placeholder?: string;
	icon?: ReactNode;
	action?: ReactNode;
	className?: string;
	type?: string;
	rules?: RegisterOptions<TFieldValues, FieldPath<TFieldValues>>;
	inputProps?: Omit<
		InputHTMLAttributes<HTMLInputElement>,
		'name' | 'value' | 'onChange' | 'onBlur' | 'ref' | 'required'
	>;
};

export function DisplayInput({ label, value }: { label: string; value: string }) {
	return (
		<FieldGroup className='w-full flex flex-col gap-1'>
			<FieldLabel>{label}:</FieldLabel>
			<Input
				value={value}
				// className="bg-gray-100 dark:bg-gray-800"
				disabled
			/>
		</FieldGroup>
	);
}

export function RhfInput<TFieldValues extends FieldValues = FieldValues>({
	name,
	label,
	placeholder,
	icon,
	action,
	className,
	type,
	rules,
	inputProps,
}: RhfInputProps<TFieldValues>) {
	const { control, formState } = useFormContext<TFieldValues>();
	if (!control) {
		throw new Error('RhfInput must be used inside a <FormProvider>.');
	}

	const {
		field,
		fieldState: { error },
	} = useController({ name, control, rules });

	const isRequired = typeof rules?.required === 'string' ? rules.required.length > 0 : Boolean(rules?.required);
	const errorForInput: FieldError | string | undefined = error;

	const Component = icon || action ? InputGroupInput : Input;

	const input = (
		<Component
			id={name as string}
			aria-invalid={!!error || undefined}
			aria-describedby={error ? `${name}-error` : undefined}
			placeholder={placeholder}
			className={className}
			type={type ?? 'text'}
			{...field}
			required={isRequired || undefined}
			disabled={inputProps?.disabled ?? formState.isSubmitting}
			{...inputProps}
		/>
	);

	return (
		<FieldGroup className='w-full flex flex-col gap-1'>
			{label && <FieldLabel htmlFor={name as string}>{label}:</FieldLabel>}

			{!icon && !action && input}
			{(icon || action) && (
				<InputGroup>
					{input}
					{icon && <InputGroupAddon>{icon}</InputGroupAddon>}
					{action && <InputGroupAddon align='inline-end'>{action}</InputGroupAddon>}
				</InputGroup>
			)}

			{error && (
				<ShadFieldError id={`${name}-error`}>
					{errorForInput && (typeof errorForInput === 'string' ? errorForInput : errorForInput.message)}
				</ShadFieldError>
			)}
		</FieldGroup>
	);
}

export function RhfPasswordInput<TFieldValues extends FieldValues = FieldValues>(
	props: Omit<RhfInputProps<TFieldValues>, 'type'>,
) {
	const [showPassword, setShowPassword] = useState(false);

	return (
		<RhfInput<TFieldValues>
			type={showPassword ? 'text' : 'password'}
			action={
				<InputGroupButton
					onClick={() => setShowPassword(!showPassword)}
					aria-label={showPassword ? 'Hide password' : 'Show password'}
				>
					{showPassword ? <EyeOff /> : <Eye />}
				</InputGroupButton>
			}
			{...props}
		/>
	);
}
