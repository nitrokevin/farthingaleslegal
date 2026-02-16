(function (wp) {
  if (!wp) {
    console.error('Column Background + XY Grid: WordPress global (wp) is undefined');
    return;
  }

  const {
    addFilter
  } = wp.hooks || {};
  const {
    __
  } = wp.i18n || {};
  const {
    Fragment,
    createElement
  } = wp.element || {};
  const {
    InspectorControls,
    MediaUpload,
    MediaUploadCheck
  } = wp.blockEditor || {};
  const {
    PanelBody,
    Button,
    ToggleControl,
    SelectControl
  } = wp.components || {};
  const {
    createHigherOrderComponent
  } = wp.compose || {};
  const {
    select
  } = wp.data || {};

  if (!addFilter || !__ || !Fragment || !createElement || !InspectorControls || !PanelBody || !Button || !ToggleControl || !SelectControl || !createHigherOrderComponent || !select) {
    console.error('Column Background + XY Grid: Missing required WordPress dependencies', {
      addFilter: !!addFilter,
      __: !!__,
      Fragment: !!Fragment,
      createElement: !!createElement,
      InspectorControls: !!InspectorControls,
      PanelBody: !!PanelBody,
      Button: !!Button,
      ToggleControl: !!ToggleControl,
      SelectControl: !!SelectControl,
      createHigherOrderComponent: !!createHigherOrderComponent,
      select: !!select
    });
    return;
  }

  const BREAKPOINTS = ['small', 'medium', 'large'];
  const GRID_OPTIONS = ['', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];

  // Extend column attributes
  addFilter('blocks.registerBlockType', 'cbg/extend-column-attributes', function (settings, name) {
    if (name !== 'core/column') return settings;
    console.log('Column Background + XY Grid: Registering attributes for core/column');
    settings.attributes = {
      ...settings.attributes,
      backgroundImage: {
        type: 'string',
        default: ''
      },
      xyGrid: {
        type: 'object',
        default: {
          small: '',
          medium: '',
          large: '',
          offsetSmall: '',
          offsetMedium: '',
          offsetLarge: ''
        }
      }
    };
    return settings;
  });

  // Extend columns attributes
  addFilter('blocks.registerBlockType', 'cbg/extend-columns-attributes', function (settings, name) {
    if (name !== 'core/columns') return settings;
    console.log('Column Background + XY Grid: Registering useFoundationGrid attribute for core/columns');
    settings.attributes = {
      ...settings.attributes,
      useFoundationGrid: {
        type: 'boolean',
        default: false
      }
    };
    return settings;
  });

  // Column Inspector controls
  const withColumnControls = createHigherOrderComponent(BlockEdit => props => {
    if (props.name !== 'core/column') return createElement(BlockEdit, props);
    const {
      attributes,
      setAttributes
    } = props;
    const {
      backgroundImage,
      xyGrid = {}
    } = attributes;

    console.log('Column Background + XY Grid: Rendering Inspector controls for core/column');

    return createElement(Fragment, null,
      createElement(BlockEdit, props),
      createElement(InspectorControls, null,
        createElement(PanelBody, {
            title: __('Background Image', 'cbg'),
            initialOpen: true
          },
          createElement(MediaUploadCheck, null,
            createElement(MediaUpload, {
              onSelect: media => setAttributes({
                backgroundImage: media.url || ''
              }),
              allowedTypes: ['image'],
              value: backgroundImage,
              render: ({
                open
              }) => createElement(Button, {
                isSecondary: true,
                onClick: open
              }, backgroundImage ? __('Change Image', 'cbg') : __('Select Image', 'cbg'))
            })
          ),
          backgroundImage && createElement(Button, {
            isLink: true,
            isDestructive: true,
            style: {
              marginTop: '10px',
              display: 'block'
            },
            onClick: () => setAttributes({
              backgroundImage: ''
            })
          }, __('Remove Image', 'cbg'))
        ),
        createElement(PanelBody, {
            title: __('Foundation XY Grid', 'cbg'),
            initialOpen: false
          },
          BREAKPOINTS.map(bp => createElement(Fragment, {
              key: bp
            },
            createElement(SelectControl, {
              label: __(bp.charAt(0).toUpperCase() + bp.slice(1) + ' Width', 'cbg'),
              value: xyGrid[bp] || '',
              options: GRID_OPTIONS.map(value => ({
                label: value || __('None', 'cbg'),
                value
              })),
              onChange: value => setAttributes({
                xyGrid: {
                  ...xyGrid,
                  [bp]: value
                }
              })
            }),
            createElement(SelectControl, {
              label: __(bp.charAt(0).toUpperCase() + bp.slice(1) + ' Offset', 'cbg'),
              value: xyGrid['offset' + bp.charAt(0).toUpperCase() + bp.slice(1)] || '',
              options: ['', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'].map(value => ({
                label: value || __('None', 'cbg'),
                value
              })),
              onChange: value => setAttributes({
                xyGrid: {
                  ...xyGrid,
                  ['offset' + bp.charAt(0).toUpperCase() + bp.slice(1)]: value
                }
              })
            })
          ))
        )
      )
    );
  }, 'withColumnControls');
  addFilter('editor.BlockEdit', 'cbg/column-inspector', withColumnControls);

  // Columns toggle
  const withColumnsToggle = createHigherOrderComponent(BlockEdit => props => {
    if (props.name !== 'core/columns') return createElement(BlockEdit, props);
    const {
      attributes,
      setAttributes
    } = props;
    const {
      useFoundationGrid = false
    } = attributes;

    console.log('Column Background + XY Grid: Rendering toggle for core/columns, useFoundationGrid:', useFoundationGrid);

    return createElement(Fragment, null,
      createElement(BlockEdit, props),
      createElement(InspectorControls, null,
        createElement(PanelBody, {
            title: __('Column Layout', 'cbg'),
            initialOpen: true
          },
          createElement(ToggleControl, {
            label: __('Use Foundation XY Grid', 'cbg'),
            checked: useFoundationGrid,
            onChange: val => {
              console.log('Column Background + XY Grid: Toggle changed to:', val);
              setAttributes({
                useFoundationGrid: val
              });
            }
          })
        )
      )
    );
  }, 'withColumnsToggle');
  addFilter('editor.BlockEdit', 'cbg/columns-toggle', withColumnsToggle);

  // Column editor preview
  const withColumnStyle = createHigherOrderComponent(BlockListBlock => props => {
    if (props.name !== 'core/column') return createElement(BlockListBlock, props);
    const {
      attributes,
      clientId
    } = props;
    const {
      backgroundImage,
      xyGrid = {},
      width
    } = attributes;

    // Get parent columns block attributes
    const parentBlock = select('core/block-editor').getBlockParents(clientId).find(id => select('core/block-editor').getBlockName(id) === 'core/columns');
    const parentAttributes = parentBlock ? select('core/block-editor').getBlockAttributes(parentBlock) : {};
    const useFoundationGrid = parentAttributes.useFoundationGrid || false;

    let xyClasses = ' cbg-xy-grid';
    if (xyGrid) {
      BREAKPOINTS.forEach(bp => {
        if (xyGrid[bp]) xyClasses += ` ${bp}-${xyGrid[bp]}`;
        const offsetKey = 'offset' + bp.charAt(0).toUpperCase() + bp.slice(1);
        if (xyGrid[offsetKey]) xyClasses += ` ${bp}-offset-${xyGrid[offsetKey]}`;
      });
    }
    if (backgroundImage) xyClasses += ' has-background-image';

    const style = {
      ...(props.wrapperProps ?.style || {})
    };
    if (backgroundImage) {
      style.backgroundImage = `url(${backgroundImage})`;
      style.backgroundSize = 'cover';
      style.backgroundPosition = 'center';
    }
    if (useFoundationGrid) {
      style.flexBasis = 'unset';
      style.flexGrow = 'unset';
    } else if (width) {
      style.flexBasis = width;
    }

    console.log('Column Background + XY Grid: Rendering core/column preview with classes:', xyClasses, 'Style:', style, 'Parent useFoundationGrid:', useFoundationGrid);

    return createElement(BlockListBlock, {
      ...props,
      wrapperProps: {
        ...props.wrapperProps,
        style,
        className: (props.wrapperProps ?.className || '') + xyClasses
      }
    });
  }, 'withColumnStyle');
  addFilter('editor.BlockListBlock', 'cbg/column-background-style', withColumnStyle);

  // Columns wrapper editor preview
  const withColumnsWrapperStyle = createHigherOrderComponent(BlockListBlock => props => {
    if (props.name !== 'core/columns') return createElement(BlockListBlock, props);
    const {
      attributes
    } = props;
    const {
      useFoundationGrid = false
    } = attributes;
    const wrapperProps = {
      ...props.wrapperProps || {}
    };
    let className = wrapperProps.className || 'wp-block-columns';

    className = className.replace(/\s*grid-x\s*grid-margin-x\s*/, '').trim();
    if (useFoundationGrid) {
      className += ' grid-x grid-margin-x';
    }

    console.log('Column Background + XY Grid: Rendering core/columns preview with className:', className, 'useFoundationGrid:', useFoundationGrid);

    return createElement(BlockListBlock, {
      ...props,
      wrapperProps: {
        ...wrapperProps,
        className
      }
    });
  }, 'withColumnsWrapperStyle');
  addFilter('editor.BlockListBlock', 'cbg/columns-wrapper-style', withColumnsWrapperStyle);
})(window.wp);