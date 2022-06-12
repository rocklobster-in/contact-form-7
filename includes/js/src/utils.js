export const absInt = string => Math.abs( parseInt( string, 10 ) );

export const dataLayerSendEvent = (eventName, customEventData) => {
    const prepareObjectFormData = prepareDataLayerFormDataObject(customEventData)
    window.dataLayer.push({
        'event': eventName,
        ...prepareObjectFormData,
    })
}       
// Convert detail.inputs informations into Object
const prepareDataLayerFormDataObject = customEventData => {
    let datLayerFormData = {}
    customEventData.inputs.forEach(input => (datLayerFormData[convertToSnakeCase(input.name)] = input.value))

    return datLayerFormData
}

// Converting string to standard to object key in snake case
const convertToSnakeCase = key => {
    return key
        .match(/[A-Z]{2,}(?=[A-Z][a-z]+[0-9]*|\b)|[A-Z]?[a-z]+[0-9]*|[A-Z]|[0-9]+/g)
        .map(x => x.toLowerCase())
        .join('_');
} 
