import { useTranslation } from 'react-i18next';
import { useAppTitle } from './useTitle';

export default function useTranslatedTitle(titleKey: string, suffix?: string, options?: any) {
	const { t } = useTranslation();
	const mainTitle = t(titleKey, options).toString();

	const fullTitle = suffix ? `${mainTitle} - ${t(suffix, options).toString()}` : mainTitle;

	useAppTitle(fullTitle);
}
