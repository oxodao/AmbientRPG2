import { type ClassValue, clsx } from 'clsx';
import i18n from 'i18next';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
	return twMerge(clsx(inputs));
}

export function setI18NLanguage(lang: string) {
	const userLanguage = lang || '/api/languages/en_US';
	const stripedLanguage = userLanguage.replace('/api/languages/', '');
	i18n.changeLanguage(stripedLanguage);
}
