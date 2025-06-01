/**
 * Internal dependencies
 */
import * as AiCapabilityExports from './ai-capability';
import * as ContentRoleExports from './content-role';
import * as ModalityExports from './modality';
import * as ServiceTypeExports from './service-type';

// Do not export the internal _VALUE_MAP constants.
const { _VALUE_MAP: _AI_CAPABILITY_VALUE_MAP, ...AiCapability } =
	AiCapabilityExports;
const { _VALUE_MAP: _CONTENT_ROLE_VALUE_MAP, ...ContentRole } =
	ContentRoleExports;
const { _VALUE_MAP: _MODALITY_VALUE_MAP, ...Modality } = ModalityExports;
const { _VALUE_MAP: _SERVICE_TYPE_VALUE_MAP, ...ServiceType } =
	ServiceTypeExports;

export { AiCapability, ContentRole, Modality, ServiceType };
