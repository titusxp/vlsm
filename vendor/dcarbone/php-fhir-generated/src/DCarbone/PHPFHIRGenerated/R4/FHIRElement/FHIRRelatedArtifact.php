<?php

namespace DCarbone\PHPFHIRGenerated\R4\FHIRElement;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: December 26th, 2019 15:44+0000
 * 
 * PHPFHIR Copyright:
 * 
 * Copyright 2016-2019 Daniel Carbone (daniel.p.carbone@gmail.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *        http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 * 
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 * 
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 * 
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 * 
 * 
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 * 
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 * 
 */

use DCarbone\PHPFHIRGenerated\R4\FHIRElement;
use DCarbone\PHPFHIRGenerated\R4\PHPFHIRConstants;
use DCarbone\PHPFHIRGenerated\R4\PHPFHIRTypeInterface;

/**
 * Related artifacts such as additional documentation, justification, or
 * bibliographic references.
 * If the element is present, it must have a value for at least one of the defined
 * elements, an \@id referenced from the Narrative, or extensions
 *
 * Class FHIRRelatedArtifact
 * @package \DCarbone\PHPFHIRGenerated\R4\FHIRElement
 */
class FHIRRelatedArtifact extends FHIRElement
{
    // name of FHIR type this class describes
    const FHIR_TYPE_NAME = PHPFHIRConstants::TYPE_NAME_RELATED_ARTIFACT;
    const FIELD_CITATION = 'citation';
    const FIELD_CITATION_EXT = '_citation';
    const FIELD_DISPLAY = 'display';
    const FIELD_DISPLAY_EXT = '_display';
    const FIELD_DOCUMENT = 'document';
    const FIELD_LABEL = 'label';
    const FIELD_LABEL_EXT = '_label';
    const FIELD_RESOURCE = 'resource';
    const FIELD_RESOURCE_EXT = '_resource';
    const FIELD_TYPE = 'type';
    const FIELD_TYPE_EXT = '_type';
    const FIELD_URL = 'url';
    const FIELD_URL_EXT = '_url';

    /** @var string */
    private $_xmlns = 'http://hl7.org/fhir';

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A bibliographic citation for the related artifact. This text SHOULD be formatted
     * according to an accepted citation format.
     *
     * @var null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRMarkdown
     */
    protected $citation = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A brief description of the document or knowledge resource being referenced,
     * suitable for display to a consumer.
     *
     * @var null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRString
     */
    protected $display = null;

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The document being referenced, represented as an attachment. This is exclusive
     * with the resource element.
     *
     * @var null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRAttachment
     */
    protected $document = null;

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short label that can be used to reference the citation from elsewhere in the
     * containing artifact, such as a footnote index.
     *
     * @var null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRString
     */
    protected $label = null;

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The related resource, such as a library, value set, profile, or other knowledge
     * resource.
     *
     * @var null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRCanonical
     */
    protected $resource = null;

    /**
     * The type of relationship to the related artifact.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of relationship to the related artifact.
     *
     * @var null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRRelatedArtifactType
     */
    protected $type = null;

    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A url for the artifact that can be followed to access the actual content.
     *
     * @var null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRUrl
     */
    protected $url = null;

    /**
     * Validation map for fields in type RelatedArtifact
     * @var array
     */
    private static $_validationRules = [    ];

    /**
     * FHIRRelatedArtifact Constructor
     * @param null|array $data
     */
    public function __construct($data = null)
    {
        if (null === $data || [] === $data) {
            return;
        }
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf(
                'FHIRRelatedArtifact::_construct - $data expected to be null or array, %s seen',
                gettype($data)
            ));
        }
        parent::__construct($data);
        if (isset($data[self::FIELD_CITATION]) || isset($data[self::FIELD_CITATION_EXT])) {
            if (isset($data[self::FIELD_CITATION])) {
                $value = $data[self::FIELD_CITATION];
            } else {
                $value = null;
            }
            if (isset($data[self::FIELD_CITATION_EXT]) && is_array($data[self::FIELD_CITATION_EXT])) {
                $ext = $data[self::FIELD_CITATION_EXT];
            } else {
                $ext = [];
            }
            if (null !== $value) {
                if ($value instanceof FHIRMarkdown) {
                    $this->setCitation($value);
                } else if (is_array($value)) {
                    $this->setCitation(new FHIRMarkdown(array_merge($ext, $value)));
                } else {
                    $this->setCitation(new FHIRMarkdown([FHIRMarkdown::FIELD_VALUE => $value] + $ext));
                }
            } else if ([] !== $ext) {
                $this->setCitation(new FHIRMarkdown($ext));
            }
        }
        if (isset($data[self::FIELD_DISPLAY]) || isset($data[self::FIELD_DISPLAY_EXT])) {
            if (isset($data[self::FIELD_DISPLAY])) {
                $value = $data[self::FIELD_DISPLAY];
            } else {
                $value = null;
            }
            if (isset($data[self::FIELD_DISPLAY_EXT]) && is_array($data[self::FIELD_DISPLAY_EXT])) {
                $ext = $data[self::FIELD_DISPLAY_EXT];
            } else {
                $ext = [];
            }
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setDisplay($value);
                } else if (is_array($value)) {
                    $this->setDisplay(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setDisplay(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } else if ([] !== $ext) {
                $this->setDisplay(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_DOCUMENT])) {
            if ($data[self::FIELD_DOCUMENT] instanceof FHIRAttachment) {
                $this->setDocument($data[self::FIELD_DOCUMENT]);
            } else {
                $this->setDocument(new FHIRAttachment($data[self::FIELD_DOCUMENT]));
            }
        }
        if (isset($data[self::FIELD_LABEL]) || isset($data[self::FIELD_LABEL_EXT])) {
            if (isset($data[self::FIELD_LABEL])) {
                $value = $data[self::FIELD_LABEL];
            } else {
                $value = null;
            }
            if (isset($data[self::FIELD_LABEL_EXT]) && is_array($data[self::FIELD_LABEL_EXT])) {
                $ext = $data[self::FIELD_LABEL_EXT];
            } else {
                $ext = [];
            }
            if (null !== $value) {
                if ($value instanceof FHIRString) {
                    $this->setLabel($value);
                } else if (is_array($value)) {
                    $this->setLabel(new FHIRString(array_merge($ext, $value)));
                } else {
                    $this->setLabel(new FHIRString([FHIRString::FIELD_VALUE => $value] + $ext));
                }
            } else if ([] !== $ext) {
                $this->setLabel(new FHIRString($ext));
            }
        }
        if (isset($data[self::FIELD_RESOURCE]) || isset($data[self::FIELD_RESOURCE_EXT])) {
            if (isset($data[self::FIELD_RESOURCE])) {
                $value = $data[self::FIELD_RESOURCE];
            } else {
                $value = null;
            }
            if (isset($data[self::FIELD_RESOURCE_EXT]) && is_array($data[self::FIELD_RESOURCE_EXT])) {
                $ext = $data[self::FIELD_RESOURCE_EXT];
            } else {
                $ext = [];
            }
            if (null !== $value) {
                if ($value instanceof FHIRCanonical) {
                    $this->setResource($value);
                } else if (is_array($value)) {
                    $this->setResource(new FHIRCanonical(array_merge($ext, $value)));
                } else {
                    $this->setResource(new FHIRCanonical([FHIRCanonical::FIELD_VALUE => $value] + $ext));
                }
            } else if ([] !== $ext) {
                $this->setResource(new FHIRCanonical($ext));
            }
        }
        if (isset($data[self::FIELD_TYPE]) || isset($data[self::FIELD_TYPE_EXT])) {
            if (isset($data[self::FIELD_TYPE])) {
                $value = $data[self::FIELD_TYPE];
            } else {
                $value = null;
            }
            if (isset($data[self::FIELD_TYPE_EXT]) && is_array($data[self::FIELD_TYPE_EXT])) {
                $ext = $data[self::FIELD_TYPE_EXT];
            } else {
                $ext = [];
            }
            if (null !== $value) {
                if ($value instanceof FHIRRelatedArtifactType) {
                    $this->setType($value);
                } else if (is_array($value)) {
                    $this->setType(new FHIRRelatedArtifactType(array_merge($ext, $value)));
                } else {
                    $this->setType(new FHIRRelatedArtifactType([FHIRRelatedArtifactType::FIELD_VALUE => $value] + $ext));
                }
            } else if ([] !== $ext) {
                $this->setType(new FHIRRelatedArtifactType($ext));
            }
        }
        if (isset($data[self::FIELD_URL]) || isset($data[self::FIELD_URL_EXT])) {
            if (isset($data[self::FIELD_URL])) {
                $value = $data[self::FIELD_URL];
            } else {
                $value = null;
            }
            if (isset($data[self::FIELD_URL_EXT]) && is_array($data[self::FIELD_URL_EXT])) {
                $ext = $data[self::FIELD_URL_EXT];
            } else {
                $ext = [];
            }
            if (null !== $value) {
                if ($value instanceof FHIRUrl) {
                    $this->setUrl($value);
                } else if (is_array($value)) {
                    $this->setUrl(new FHIRUrl(array_merge($ext, $value)));
                } else {
                    $this->setUrl(new FHIRUrl([FHIRUrl::FIELD_VALUE => $value] + $ext));
                }
            } else if ([] !== $ext) {
                $this->setUrl(new FHIRUrl($ext));
            }
        }
    }

    /**
     * @return string
     */
    public function _getFHIRTypeName()
    {
        return self::FHIR_TYPE_NAME;
    }

    /**
     * @return string
     */
    public function _getFHIRXMLElementDefinition()
    {
        $xmlns = $this->_getFHIRXMLNamespace();
        if (null !== $xmlns) {
            $xmlns = " xmlns=\"{$xmlns}\"";
        }
        return "<RelatedArtifact{$xmlns}></RelatedArtifact>";
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A bibliographic citation for the related artifact. This text SHOULD be formatted
     * according to an accepted citation format.
     *
     * @return null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRMarkdown
     */
    public function getCitation()
    {
        return $this->citation;
    }

    /**
     * A string that may contain Github Flavored Markdown syntax for optional
     * processing by a mark down presentation engine
     * Systems are not required to have markdown support, so the text should be
     * readable without markdown processing. The markdown syntax is GFM - see
     * https://github.github.com/gfm/
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A bibliographic citation for the related artifact. This text SHOULD be formatted
     * according to an accepted citation format.
     *
     * @param null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRMarkdown $citation
     * @return static
     */
    public function setCitation($citation = null)
    {
        if (null === $citation) {
            $this->citation = null;
            return $this;
        }
        if ($citation instanceof FHIRMarkdown) {
            $this->citation = $citation;
            return $this;
        }
        $this->citation = new FHIRMarkdown($citation);
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A brief description of the document or knowledge resource being referenced,
     * suitable for display to a consumer.
     *
     * @return null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRString
     */
    public function getDisplay()
    {
        return $this->display;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A brief description of the document or knowledge resource being referenced,
     * suitable for display to a consumer.
     *
     * @param null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRString $display
     * @return static
     */
    public function setDisplay($display = null)
    {
        if (null === $display) {
            $this->display = null;
            return $this;
        }
        if ($display instanceof FHIRString) {
            $this->display = $display;
            return $this;
        }
        $this->display = new FHIRString($display);
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The document being referenced, represented as an attachment. This is exclusive
     * with the resource element.
     *
     * @return null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRAttachment
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The document being referenced, represented as an attachment. This is exclusive
     * with the resource element.
     *
     * @param null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRAttachment $document
     * @return static
     */
    public function setDocument(FHIRAttachment $document = null)
    {
        $this->document = $document;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short label that can be used to reference the citation from elsewhere in the
     * containing artifact, such as a footnote index.
     *
     * @return null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRString
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short label that can be used to reference the citation from elsewhere in the
     * containing artifact, such as a footnote index.
     *
     * @param null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRString $label
     * @return static
     */
    public function setLabel($label = null)
    {
        if (null === $label) {
            $this->label = null;
            return $this;
        }
        if ($label instanceof FHIRString) {
            $this->label = $label;
            return $this;
        }
        $this->label = new FHIRString($label);
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The related resource, such as a library, value set, profile, or other knowledge
     * resource.
     *
     * @return null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRCanonical
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The related resource, such as a library, value set, profile, or other knowledge
     * resource.
     *
     * @param null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRCanonical $resource
     * @return static
     */
    public function setResource($resource = null)
    {
        if (null === $resource) {
            $this->resource = null;
            return $this;
        }
        if ($resource instanceof FHIRCanonical) {
            $this->resource = $resource;
            return $this;
        }
        $this->resource = new FHIRCanonical($resource);
        return $this;
    }

    /**
     * The type of relationship to the related artifact.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of relationship to the related artifact.
     *
     * @return null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRRelatedArtifactType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * The type of relationship to the related artifact.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The type of relationship to the related artifact.
     *
     * @param null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRRelatedArtifactType $type
     * @return static
     */
    public function setType(FHIRRelatedArtifactType $type = null)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A url for the artifact that can be followed to access the actual content.
     *
     * @return null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRUrl
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * A URI that is a literal reference
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * A url for the artifact that can be followed to access the actual content.
     *
     * @param null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRUrl $url
     * @return static
     */
    public function setUrl($url = null)
    {
        if (null === $url) {
            $this->url = null;
            return $this;
        }
        if ($url instanceof FHIRUrl) {
            $this->url = $url;
            return $this;
        }
        $this->url = new FHIRUrl($url);
        return $this;
    }

    /**
     * Returns the validation rules that this type's fields must comply with to be considered "valid"
     * The returned array is in ["fieldname[.offset]" => ["rule" => {constraint}]]
     *
     * @return array
     */
    public function _getValidationRules()
    {
        return self::$_validationRules;
    }

    /**
     * Validates that this type conforms to the specifications set forth for it by FHIR.  An empty array must be seen as
     * passing.
     *
     * @return array
     */
    public function _getValidationErrors()
    {
        $errs = parent::_getValidationErrors();
        $validationRules = $this->_getValidationRules();
        if (null !== ($v = $this->getCitation())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_CITATION] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getDisplay())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DISPLAY] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getDocument())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_DOCUMENT] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getLabel())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_LABEL] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getResource())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_RESOURCE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getType())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_TYPE] = $fieldErrs;
            }
        }
        if (null !== ($v = $this->getUrl())) {
            if ([] !== ($fieldErrs = $v->_getValidationErrors())) {
                $errs[self::FIELD_URL] = $fieldErrs;
            }
        }
        if (isset($validationRules[self::FIELD_CITATION])) {
            $v = $this->getCitation();
            foreach($validationRules[self::FIELD_CITATION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RELATED_ARTIFACT, self::FIELD_CITATION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_CITATION])) {
                        $errs[self::FIELD_CITATION] = [];
                    }
                    $errs[self::FIELD_CITATION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DISPLAY])) {
            $v = $this->getDisplay();
            foreach($validationRules[self::FIELD_DISPLAY] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RELATED_ARTIFACT, self::FIELD_DISPLAY, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DISPLAY])) {
                        $errs[self::FIELD_DISPLAY] = [];
                    }
                    $errs[self::FIELD_DISPLAY][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_DOCUMENT])) {
            $v = $this->getDocument();
            foreach($validationRules[self::FIELD_DOCUMENT] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RELATED_ARTIFACT, self::FIELD_DOCUMENT, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_DOCUMENT])) {
                        $errs[self::FIELD_DOCUMENT] = [];
                    }
                    $errs[self::FIELD_DOCUMENT][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_LABEL])) {
            $v = $this->getLabel();
            foreach($validationRules[self::FIELD_LABEL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RELATED_ARTIFACT, self::FIELD_LABEL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_LABEL])) {
                        $errs[self::FIELD_LABEL] = [];
                    }
                    $errs[self::FIELD_LABEL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_RESOURCE])) {
            $v = $this->getResource();
            foreach($validationRules[self::FIELD_RESOURCE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RELATED_ARTIFACT, self::FIELD_RESOURCE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_RESOURCE])) {
                        $errs[self::FIELD_RESOURCE] = [];
                    }
                    $errs[self::FIELD_RESOURCE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_TYPE])) {
            $v = $this->getType();
            foreach($validationRules[self::FIELD_TYPE] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RELATED_ARTIFACT, self::FIELD_TYPE, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_TYPE])) {
                        $errs[self::FIELD_TYPE] = [];
                    }
                    $errs[self::FIELD_TYPE][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_URL])) {
            $v = $this->getUrl();
            foreach($validationRules[self::FIELD_URL] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_RELATED_ARTIFACT, self::FIELD_URL, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_URL])) {
                        $errs[self::FIELD_URL] = [];
                    }
                    $errs[self::FIELD_URL][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_EXTENSION])) {
            $v = $this->getExtension();
            foreach($validationRules[self::FIELD_EXTENSION] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_EXTENSION, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_EXTENSION])) {
                        $errs[self::FIELD_EXTENSION] = [];
                    }
                    $errs[self::FIELD_EXTENSION][$rule] = $err;
                }
            }
        }
        if (isset($validationRules[self::FIELD_ID])) {
            $v = $this->getId();
            foreach($validationRules[self::FIELD_ID] as $rule => $constraint) {
                $err = $this->_performValidation(PHPFHIRConstants::TYPE_NAME_ELEMENT, self::FIELD_ID, $rule, $constraint, $v);
                if (null !== $err) {
                    if (!isset($errs[self::FIELD_ID])) {
                        $errs[self::FIELD_ID] = [];
                    }
                    $errs[self::FIELD_ID][$rule] = $err;
                }
            }
        }
        return $errs;
    }

    /**
     * @param \SimpleXMLElement|string|null $sxe
     * @param null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRRelatedArtifact $type
     * @param null|int $libxmlOpts
     * @return null|\DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRRelatedArtifact
     */
    public static function xmlUnserialize($sxe = null, PHPFHIRTypeInterface $type = null, $libxmlOpts = 591872)
    {
        if (null === $sxe) {
            return null;
        }
        if (is_string($sxe)) {
            libxml_use_internal_errors(true);
            $sxe = new \SimpleXMLElement($sxe, $libxmlOpts, false);
            if ($sxe === false) {
                throw new \DomainException(sprintf('FHIRRelatedArtifact::xmlUnserialize - String provided is not parseable as XML: %s', implode(', ', array_map(function(\libXMLError $err) { return $err->message; }, libxml_get_errors()))));
            }
            libxml_use_internal_errors(false);
        }
        if (!($sxe instanceof \SimpleXMLElement)) {
            throw new \InvalidArgumentException(sprintf('FHIRRelatedArtifact::xmlUnserialize - $sxe value must be null, \\SimpleXMLElement, or valid XML string, %s seen', gettype($sxe)));
        }
        if (null === $type) {
            $type = new FHIRRelatedArtifact;
        } elseif (!is_object($type) || !($type instanceof FHIRRelatedArtifact)) {
            throw new \RuntimeException(sprintf(
                'FHIRRelatedArtifact::xmlUnserialize - $type must be instance of \DCarbone\PHPFHIRGenerated\R4\FHIRElement\FHIRRelatedArtifact or null, %s seen.',
                is_object($type) ? get_class($type) : gettype($type)
            ));
        }
        FHIRElement::xmlUnserialize($sxe, $type);
        $xmlNamespaces = $sxe->getDocNamespaces(false, false);
        if ([] !== $xmlNamespaces) {
            $ns = reset($xmlNamespaces);
            if (false !== $ns && '' !== $ns) {
                $type->_xmlns = $ns;
            }
        }
        $attributes = $sxe->attributes();
        $children = $sxe->children();
        if (isset($children->citation)) {
            $type->setCitation(FHIRMarkdown::xmlUnserialize($children->citation));
        }
        if (isset($attributes->citation)) {
            $pt = $type->getCitation();
            if (null !== $pt) {
                $pt->setValue((string)$attributes->citation);
            } else {
                $type->setCitation((string)$attributes->citation);
            }
        }
        if (isset($children->display)) {
            $type->setDisplay(FHIRString::xmlUnserialize($children->display));
        }
        if (isset($attributes->display)) {
            $pt = $type->getDisplay();
            if (null !== $pt) {
                $pt->setValue((string)$attributes->display);
            } else {
                $type->setDisplay((string)$attributes->display);
            }
        }
        if (isset($children->document)) {
            $type->setDocument(FHIRAttachment::xmlUnserialize($children->document));
        }
        if (isset($children->label)) {
            $type->setLabel(FHIRString::xmlUnserialize($children->label));
        }
        if (isset($attributes->label)) {
            $pt = $type->getLabel();
            if (null !== $pt) {
                $pt->setValue((string)$attributes->label);
            } else {
                $type->setLabel((string)$attributes->label);
            }
        }
        if (isset($children->resource)) {
            $type->setResource(FHIRCanonical::xmlUnserialize($children->resource));
        }
        if (isset($attributes->resource)) {
            $pt = $type->getResource();
            if (null !== $pt) {
                $pt->setValue((string)$attributes->resource);
            } else {
                $type->setResource((string)$attributes->resource);
            }
        }
        if (isset($children->type)) {
            $type->setType(FHIRRelatedArtifactType::xmlUnserialize($children->type));
        }
        if (isset($children->url)) {
            $type->setUrl(FHIRUrl::xmlUnserialize($children->url));
        }
        if (isset($attributes->url)) {
            $pt = $type->getUrl();
            if (null !== $pt) {
                $pt->setValue((string)$attributes->url);
            } else {
                $type->setUrl((string)$attributes->url);
            }
        }
        return $type;
    }

    /**
     * @param null|\SimpleXMLElement $sxe
     * @param null|int $libxmlOpts
     * @return \SimpleXMLElement
     */
    public function xmlSerialize(\SimpleXMLElement $sxe = null, $libxmlOpts = 591872)
    {
        if (null === $sxe) {
            $sxe = new \SimpleXMLElement($this->_getFHIRXMLElementDefinition(), $libxmlOpts, false);
        }
        parent::xmlSerialize($sxe);
        if (null !== ($v = $this->getCitation())) {
            $v->xmlSerialize($sxe->addChild(self::FIELD_CITATION, null, $v->_getFHIRXMLNamespace()));
        }
        if (null !== ($v = $this->getDisplay())) {
            $v->xmlSerialize($sxe->addChild(self::FIELD_DISPLAY, null, $v->_getFHIRXMLNamespace()));
        }
        if (null !== ($v = $this->getDocument())) {
            $v->xmlSerialize($sxe->addChild(self::FIELD_DOCUMENT, null, $v->_getFHIRXMLNamespace()));
        }
        if (null !== ($v = $this->getLabel())) {
            $v->xmlSerialize($sxe->addChild(self::FIELD_LABEL, null, $v->_getFHIRXMLNamespace()));
        }
        if (null !== ($v = $this->getResource())) {
            $v->xmlSerialize($sxe->addChild(self::FIELD_RESOURCE, null, $v->_getFHIRXMLNamespace()));
        }
        if (null !== ($v = $this->getType())) {
            $v->xmlSerialize($sxe->addChild(self::FIELD_TYPE, null, $v->_getFHIRXMLNamespace()));
        }
        if (null !== ($v = $this->getUrl())) {
            $v->xmlSerialize($sxe->addChild(self::FIELD_URL, null, $v->_getFHIRXMLNamespace()));
        }
        return $sxe;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $a = parent::jsonSerialize();
        if (null !== ($v = $this->getCitation())) {
            $a[self::FIELD_CITATION] = $v->getValue();
            $enc = $v->jsonSerialize();
            $cnt = count($enc);
            if (0 < $cnt && (1 !== $cnt || (1 === $cnt && !array_key_exists(FHIRMarkdown::FIELD_VALUE, $enc)))) {
                unset($enc[FHIRMarkdown::FIELD_VALUE]);
                $a[self::FIELD_CITATION_EXT] = $enc;
            }
        }
        if (null !== ($v = $this->getDisplay())) {
            $a[self::FIELD_DISPLAY] = $v->getValue();
            $enc = $v->jsonSerialize();
            $cnt = count($enc);
            if (0 < $cnt && (1 !== $cnt || (1 === $cnt && !array_key_exists(FHIRString::FIELD_VALUE, $enc)))) {
                unset($enc[FHIRString::FIELD_VALUE]);
                $a[self::FIELD_DISPLAY_EXT] = $enc;
            }
        }
        if (null !== ($v = $this->getDocument())) {
            $a[self::FIELD_DOCUMENT] = $v;
        }
        if (null !== ($v = $this->getLabel())) {
            $a[self::FIELD_LABEL] = $v->getValue();
            $enc = $v->jsonSerialize();
            $cnt = count($enc);
            if (0 < $cnt && (1 !== $cnt || (1 === $cnt && !array_key_exists(FHIRString::FIELD_VALUE, $enc)))) {
                unset($enc[FHIRString::FIELD_VALUE]);
                $a[self::FIELD_LABEL_EXT] = $enc;
            }
        }
        if (null !== ($v = $this->getResource())) {
            $a[self::FIELD_RESOURCE] = $v->getValue();
            $enc = $v->jsonSerialize();
            $cnt = count($enc);
            if (0 < $cnt && (1 !== $cnt || (1 === $cnt && !array_key_exists(FHIRCanonical::FIELD_VALUE, $enc)))) {
                unset($enc[FHIRCanonical::FIELD_VALUE]);
                $a[self::FIELD_RESOURCE_EXT] = $enc;
            }
        }
        if (null !== ($v = $this->getType())) {
            $a[self::FIELD_TYPE] = $v->getValue();
            $enc = $v->jsonSerialize();
            $cnt = count($enc);
            if (0 < $cnt && (1 !== $cnt || (1 === $cnt && !array_key_exists(FHIRRelatedArtifactType::FIELD_VALUE, $enc)))) {
                unset($enc[FHIRRelatedArtifactType::FIELD_VALUE]);
                $a[self::FIELD_TYPE_EXT] = $enc;
            }
        }
        if (null !== ($v = $this->getUrl())) {
            $a[self::FIELD_URL] = $v->getValue();
            $enc = $v->jsonSerialize();
            $cnt = count($enc);
            if (0 < $cnt && (1 !== $cnt || (1 === $cnt && !array_key_exists(FHIRUrl::FIELD_VALUE, $enc)))) {
                unset($enc[FHIRUrl::FIELD_VALUE]);
                $a[self::FIELD_URL_EXT] = $enc;
            }
        }
        if ([] !== ($vs = $this->_getFHIRComments())) {
            $a[PHPFHIRConstants::JSON_FIELD_FHIR_COMMENTS] = $vs;
        }
        return $a;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return self::FHIR_TYPE_NAME;
    }
}