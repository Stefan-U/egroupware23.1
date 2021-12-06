import {et2_IInput, et2_IInputNode} from "../et2_core_interfaces";
import {Et2Widget} from "../Et2Widget/Et2Widget";
import {dedupeMixin} from "@lion/core";

/**
 * This mixin will allow any LitElement to become an Et2InputWidget
 *
 * Usage:
 * export class Et2Button extends Et2InputWidget(LitWidget)) {...}
 */

/**
 * Need to define the interface first, to get around TypeScript issues with protected/public
 * This must match the public API for Et2InputWidgetClass
 * @see https://lit.dev/docs/composition/mixins/#typing-the-subclass
 */
export declare class Et2InputWidgetInterface
{
	readOnly : boolean;
	protected value : string | number | Object;

	public set_value(any) : void;

	public get_value() : any;

	public getValue() : any;

	public isDirty() : boolean;

	public resetDirty() : void;

	public isValid(messages : string[]) : boolean;
}

const Et2InputWidgetMixin = (superclass) =>
{
	class Et2InputWidgetClass extends Et2Widget(superclass) implements et2_IInput, et2_IInputNode
	{
		protected value : string | number | Object;
		protected _oldValue : string | number | Object;
		protected node : HTMLElement;

		/** WebComponent **/
		static get styles()
		{
			return [
				...super.styles
			];
		}

		static get properties()
		{
			return {
				...super.properties,
				// readOnly is what the property is in Lion, readonly is the attribute
				readOnly: {
					type: Boolean,
					attribute: 'readonly',
					reflect: true,
				},
				// readonly is what is in the templates
				// I put this in here so loadWebComponent finds it when it tries to set it from the template
				readonly: {
					type: Boolean
				}
			};
		}

		constructor(...args : any[])
		{
			super(...args);

		}
		connectedCallback()
		{
			super.connectedCallback();
			this.node = this.getInputNode();
		}
		set_value(new_value)
		{
			this.value = new_value;
		}

		get_value()
		{
			return this.getValue();
		}

		getValue()
		{
			return typeof this.serializedValue !== "undefined" ? this.serializedValue : this.modalValue;
		}


		isDirty()
		{
			let value = this.getValue();
			if(typeof value !== typeof this._oldValue)
			{
				return true;
			}
			if(this._oldValue === value)
			{
				return false;
			}
			switch(typeof this._oldValue)
			{
				case "object":
					if(Array.isArray(this._oldValue) &&
						this._oldValue.length !== value.length
					)
					{
						return true;
					}
					for(let key in this._oldValue)
					{
						if(this._oldValue[key] !== value[key])
						{
							return true;
						}
					}
					return false;
				default:
					return this._oldValue != value;
			}
		}

		resetDirty()
		{
			this._oldValue = this.getValue();
		}

		isValid(messages)
		{
			var ok = true;

			// Check for required
			if(this.options && this.options.needed && !this.options.readonly && !this.disabled &&
				(this.getValue() == null || this.getValue().valueOf() == ''))
			{
				messages.push(this.egw().lang('Field must not be empty !!!'));
				ok = false;
			}
			return ok;
		}

		getInputNode()
		{
			// From LionInput
			return this._inputNode;
		}
	}

	return Et2InputWidgetClass;
}
export const Et2InputWidget = dedupeMixin(Et2InputWidgetMixin);