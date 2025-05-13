type Option = {
	value: string;
	label: string;
	id?: string;
	[ key: string ]: string | undefined;
};

export type MultiCheckboxControlProps = {
	label: string;
	value: string[];
	options: Option[];
	onChange?: ( value: string[] ) => void;
	onToggle?: ( value: string ) => void;
	showFilter?: boolean;
	searchLabel?: string; // Must be provided if `showFilter` is true.
	id?: string;
	className?: string;
	help?: string;
	hideLabelFromVision?: boolean;
	__nextHasNoMarginBottom?: boolean;
};
