# Change Log

## [v6.1.1](https://github.com/prooph/service-bus/tree/v6.1.1)

[Full Changelog](https://github.com/prooph/service-bus/compare/v6.1.0...v6.1.1)

**Fixed bugs:**

- Bug in OnEventStrategy [\#169](https://github.com/prooph/service-bus/issues/169)
- Fix Bugs in Event Bus [\#171](https://github.com/prooph/service-bus/pull/171) ([prolic](https://github.com/prolic))

**Merged pull requests:**

- Merge readme into overview and add live coding video [\#170](https://github.com/prooph/service-bus/pull/170) ([codeliner](https://github.com/codeliner))
- Make small grammar changes to docs [\#168](https://github.com/prooph/service-bus/pull/168) ([camuthig](https://github.com/camuthig))

## [v5.2.1](https://github.com/prooph/service-bus/tree/v5.2.1) (2017-10-23)
[Full Changelog](https://github.com/prooph/service-bus/compare/v6.1.0...v5.2.1)

**Merged pull requests:**

- Catch throwable and reset isDispatching [\#172](https://github.com/prooph/service-bus/pull/172) ([codeliner](https://github.com/codeliner))

## [v6.1.0](https://github.com/prooph/service-bus/tree/v6.1.0) (2017-09-29)
[Full Changelog](https://github.com/prooph/service-bus/compare/v6.0.3...v6.1.0)

**Implemented enhancements:**

- AsyncMessage now implements the Message interface [\#167](https://github.com/prooph/service-bus/pull/167) ([iainmckay](https://github.com/iainmckay))
- Optionally collect listener exceptions [\#166](https://github.com/prooph/service-bus/pull/166) ([codeliner](https://github.com/codeliner))

**Closed issues:**

- Exception in event listener stops propagating event to other registered listeners  [\#165](https://github.com/prooph/service-bus/issues/165)

**Merged pull requests:**

- \[doc\] Add enqueue producer to the list of available solutions. [\#162](https://github.com/prooph/service-bus/pull/162) ([makasim](https://github.com/makasim))

## [v6.0.3](https://github.com/prooph/service-bus/tree/v6.0.3) (2017-07-02)
[Full Changelog](https://github.com/prooph/service-bus/compare/v6.0.2...v6.0.3)

**Fixed bugs:**

- avoid array\_merge due to appending [\#164](https://github.com/prooph/service-bus/pull/164) ([basz](https://github.com/basz))

## [v6.0.2](https://github.com/prooph/service-bus/tree/v6.0.2) (2017-06-27)
[Full Changelog](https://github.com/prooph/service-bus/compare/v6.0.1...v6.0.2)

**Fixed bugs:**

- fix service locator plugin [\#161](https://github.com/prooph/service-bus/pull/161) ([prolic](https://github.com/prolic))

**Closed issues:**

- Servicelocator deletes listeners [\#160](https://github.com/prooph/service-bus/issues/160)

## [v6.0.1](https://github.com/prooph/service-bus/tree/v6.0.1) (2017-04-24)
[Full Changelog](https://github.com/prooph/service-bus/compare/v6.0.0...v6.0.1)

**Fixed bugs:**

- Do not process already process messages with a custom strategy [\#159](https://github.com/prooph/service-bus/pull/159) ([lunetics](https://github.com/lunetics))

## [v6.0.0](https://github.com/prooph/service-bus/tree/v6.0.0) (2017-02-16)
[Full Changelog](https://github.com/prooph/service-bus/compare/v6.0.0-beta3...v6.0.0)

**Implemented enhancements:**

- Get rid of the AbstractInvokeStrategy [\#152](https://github.com/prooph/service-bus/issues/152)
- update to use psr\container [\#155](https://github.com/prooph/service-bus/pull/155) ([prolic](https://github.com/prolic))
- remove AbstractInvokeStrategy [\#153](https://github.com/prooph/service-bus/pull/153) ([prolic](https://github.com/prolic))

**Fixed bugs:**

- Bugfix: exception is not resettable with finalize event [\#154](https://github.com/prooph/service-bus/pull/154) ([oqq](https://github.com/oqq))

## [v6.0.0-beta3](https://github.com/prooph/service-bus/tree/v6.0.0-beta3) (2017-01-12)
[Full Changelog](https://github.com/prooph/service-bus/compare/v6.0.0-beta2...v6.0.0-beta3)

## [v6.0.0-beta2](https://github.com/prooph/service-bus/tree/v6.0.0-beta2) (2017-01-12)
[Full Changelog](https://github.com/prooph/service-bus/compare/v6.0.0-beta1...v6.0.0-beta2)

**Implemented enhancements:**

- New plugin registration [\#148](https://github.com/prooph/service-bus/pull/148) ([prolic](https://github.com/prolic))

**Fixed bugs:**

- OnEventInvokeStrategyPlugin broken [\#150](https://github.com/prooph/service-bus/issues/150)
- Fix OnEventStrategy [\#151](https://github.com/prooph/service-bus/pull/151) ([mablae](https://github.com/mablae))

**Closed issues:**

- How to deal with promises [\#145](https://github.com/prooph/service-bus/issues/145)

**Merged pull requests:**

- Update docs [\#149](https://github.com/prooph/service-bus/pull/149) ([prolic](https://github.com/prolic))
- Docs [\#146](https://github.com/prooph/service-bus/pull/146) ([prolic](https://github.com/prolic))

## [v6.0.0-beta1](https://github.com/prooph/service-bus/tree/v6.0.0-beta1) (2016-12-13)
[Full Changelog](https://github.com/prooph/service-bus/compare/v5.2.0...v6.0.0-beta1)

**Implemented enhancements:**

- Provide a new plugin that routes messages based on container information [\#106](https://github.com/prooph/service-bus/issues/106)
- Assert action event name on plugin attach [\#105](https://github.com/prooph/service-bus/issues/105)
- update command bus to use only two events [\#144](https://github.com/prooph/service-bus/pull/144) ([prolic](https://github.com/prolic))
- Handle custom message names better [\#143](https://github.com/prooph/service-bus/pull/143) ([prolic](https://github.com/prolic))
- add ServiceLocatorEventRouter & SingleHandlerServiceLocatorRouter [\#142](https://github.com/prooph/service-bus/pull/142) ([prolic](https://github.com/prolic))
- Support for PHP 7.1 [\#140](https://github.com/prooph/service-bus/pull/140) ([prolic](https://github.com/prolic))
- update to docheader v0.1.3 [\#138](https://github.com/prooph/service-bus/pull/138) ([prolic](https://github.com/prolic))

**Closed issues:**

- OnEventStrategy needs to handle custom message names better [\#109](https://github.com/prooph/service-bus/issues/109)

**Merged pull requests:**

- fixed typo - "bridge" [\#141](https://github.com/prooph/service-bus/pull/141) ([sasezaki](https://github.com/sasezaki))

## [v5.2.0](https://github.com/prooph/service-bus/tree/v5.2.0) (2016-09-02)
[Full Changelog](https://github.com/prooph/service-bus/compare/v5.1.0...v5.2.0)

**Implemented enhancements:**

- UnauthorizedException not informative [\#136](https://github.com/prooph/service-bus/issues/136)
- Add an AsyncSwitchMessageRouter [\#128](https://github.com/prooph/service-bus/issues/128)
- react/promise dependency should be optional [\#126](https://github.com/prooph/service-bus/issues/126)
- Add amqp message dispatcher [\#60](https://github.com/prooph/service-bus/issues/60)
- expose message name in UnauthorizedException if enabled [\#137](https://github.com/prooph/service-bus/pull/137) ([prolic](https://github.com/prolic))
- bus factory async option [\#135](https://github.com/prooph/service-bus/pull/135) ([codeliner](https://github.com/codeliner))
- Add event bus support to AsyncSwitchMessageRouter [\#133](https://github.com/prooph/service-bus/pull/133) ([codeliner](https://github.com/codeliner))
- Updated Async router with added interface [\#132](https://github.com/prooph/service-bus/pull/132) ([guyradford](https://github.com/guyradford))
- Add MessageBusRouterPlugin interface [\#131](https://github.com/prooph/service-bus/pull/131) ([codeliner](https://github.com/codeliner))

## [v5.1.0](https://github.com/prooph/service-bus/tree/v5.1.0) (2016-05-08)
[Full Changelog](https://github.com/prooph/service-bus/compare/v5.0.3...v5.1.0)

**Implemented enhancements:**

- mark factories as final [\#117](https://github.com/prooph/service-bus/pull/117) ([sandrokeil](https://github.com/sandrokeil))
- update factories to interop-config 1.0, add static factory support [\#116](https://github.com/prooph/service-bus/pull/116) ([sandrokeil](https://github.com/sandrokeil))

**Closed issues:**

- Update to coveralls ^1.0 [\#113](https://github.com/prooph/service-bus/issues/113)

**Merged pull requests:**

- Prepare 5.1 Release [\#124](https://github.com/prooph/service-bus/pull/124) ([codeliner](https://github.com/codeliner))
- fix a type on "therefore" [\#123](https://github.com/prooph/service-bus/pull/123) ([christickner](https://github.com/christickner))
- Fix typo in plugins.md [\#121](https://github.com/prooph/service-bus/pull/121) ([jpkleemans](https://github.com/jpkleemans))
- $foo? [\#119](https://github.com/prooph/service-bus/pull/119) ([basz](https://github.com/basz))
- remove final keyword from factories to ensure BC and add check for containerId method [\#118](https://github.com/prooph/service-bus/pull/118) ([sandrokeil](https://github.com/sandrokeil))

## [v5.0.3](https://github.com/prooph/service-bus/tree/v5.0.3) (2016-02-10)
[Full Changelog](https://github.com/prooph/service-bus/compare/v5.0.2...v5.0.3)

**Fixed bugs:**

- Bugfix: Correctly detect message name in FinderInvokeStrategy and Han… [\#110](https://github.com/prooph/service-bus/pull/110) ([prolic](https://github.com/prolic))
- Resolve \#107 - Correctly detect event name in OnEventStrategy [\#108](https://github.com/prooph/service-bus/pull/108) ([robertlemke](https://github.com/robertlemke))

**Closed issues:**

- OnEventStrategy ignores method name returned by messageName\(\) [\#107](https://github.com/prooph/service-bus/issues/107)
- `Prooph\ServiceBus\Plugin`ServiceLocatorPlugin` is not compatible with the `EventBus` [\#104](https://github.com/prooph/service-bus/issues/104)

**Merged pull requests:**

- Bump php-coveralls to 1.0 [\#115](https://github.com/prooph/service-bus/pull/115) ([codeliner](https://github.com/codeliner))
- Hotfix on example code [\#112](https://github.com/prooph/service-bus/pull/112) ([malukenho](https://github.com/malukenho))
- Exceptions are all wrapped from the command bus. [\#111](https://github.com/prooph/service-bus/pull/111) ([bweston92](https://github.com/bweston92))

## [v5.0.2](https://github.com/prooph/service-bus/tree/v5.0.2) (2015-12-18)
[Full Changelog](https://github.com/prooph/service-bus/compare/v5.0.1...v5.0.2)

**Closed issues:**

- AbstractBusFactory attachPlugins uses hardcoded vendor name iso using vendorName\(\) [\#102](https://github.com/prooph/service-bus/issues/102)

**Merged pull requests:**

- Fixes \#102 [\#103](https://github.com/prooph/service-bus/pull/103) ([DannyvdSluijs](https://github.com/DannyvdSluijs))

## [v5.0.1](https://github.com/prooph/service-bus/tree/v5.0.1) (2015-11-22)
[Full Changelog](https://github.com/prooph/service-bus/compare/v5.0...v5.0.1)

## [v5.0](https://github.com/prooph/service-bus/tree/v5.0) (2015-11-22)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.6...v5.0)

**Implemented enhancements:**

- Throw exception if no handler found or handler cannot handle message [\#87](https://github.com/prooph/service-bus/issues/87)
- Change default behavior of message bus when message is a string [\#84](https://github.com/prooph/service-bus/issues/84)
- Queue commands and pass pending commands to exception [\#97](https://github.com/prooph/service-bus/pull/97) ([codeliner](https://github.com/codeliner))
- Handle string messsages without plugin [\#91](https://github.com/prooph/service-bus/pull/91) ([codeliner](https://github.com/codeliner))

**Fixed bugs:**

- Query bus plugins cannot add to the chain. [\#95](https://github.com/prooph/service-bus/issues/95)

**Merged pull requests:**

- v5.0 [\#101](https://github.com/prooph/service-bus/pull/101) ([codeliner](https://github.com/codeliner))
- Fix incorrect test [\#100](https://github.com/prooph/service-bus/pull/100) ([codeliner](https://github.com/codeliner))
- Bookdown docs [\#99](https://github.com/prooph/service-bus/pull/99) ([codeliner](https://github.com/codeliner))
- Let plugins replace promise before it is returned [\#98](https://github.com/prooph/service-bus/pull/98) ([codeliner](https://github.com/codeliner))
- updated bookdown templates to version 0.2.0 [\#96](https://github.com/prooph/service-bus/pull/96) ([sandrokeil](https://github.com/sandrokeil))
- added bookdown.io documentation [\#93](https://github.com/prooph/service-bus/pull/93) ([sandrokeil](https://github.com/sandrokeil))
- Check param message-handled and throw ex in false case [\#92](https://github.com/prooph/service-bus/pull/92) ([codeliner](https://github.com/codeliner))
- Cleanup and tests for service bus [\#90](https://github.com/prooph/service-bus/pull/90) ([prolic](https://github.com/prolic))
- Always trigger invoke handler event [\#89](https://github.com/prooph/service-bus/pull/89) ([codeliner](https://github.com/codeliner))
- Develop [\#88](https://github.com/prooph/service-bus/pull/88) ([prolic](https://github.com/prolic))

## [v4.6](https://github.com/prooph/service-bus/tree/v4.6) (2015-10-21)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.5...v4.6)

**Implemented enhancements:**

- Feature/string message initializer plugin [\#82](https://github.com/prooph/service-bus/pull/82) ([bweston92](https://github.com/bweston92))
- route guard plugin adds message as context param [\#78](https://github.com/prooph/service-bus/pull/78) ([prolic](https://github.com/prolic))
- Implemented Interop Config Library [\#77](https://github.com/prooph/service-bus/pull/77) ([sandrokeil](https://github.com/sandrokeil))
- fix namespace organisation in tests [\#75](https://github.com/prooph/service-bus/pull/75) ([prolic](https://github.com/prolic))

**Closed issues:**

- Add StringMessageInitializerPlugin to list of plugins [\#83](https://github.com/prooph/service-bus/issues/83)
- Documentaton: route guard plugin adds message as context param [\#79](https://github.com/prooph/service-bus/issues/79)

**Merged pull requests:**

- Merge v4.6 changes [\#86](https://github.com/prooph/service-bus/pull/86) ([codeliner](https://github.com/codeliner))
- Documentaton: route guard plugin [\#80](https://github.com/prooph/service-bus/pull/80) ([prolic](https://github.com/prolic))
- Remove author tag with wrong email [\#76](https://github.com/prooph/service-bus/pull/76) ([codeliner](https://github.com/codeliner))

## [v4.5](https://github.com/prooph/service-bus/tree/v4.5) (2015-10-06)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.4.1...v4.5)

**Merged pull requests:**

- Add message producer plugin [\#74](https://github.com/prooph/service-bus/pull/74) ([codeliner](https://github.com/codeliner))

## [v4.4.1](https://github.com/prooph/service-bus/tree/v4.4.1) (2015-10-02)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.4...v4.4.1)

**Fixed bugs:**

- fix exception message [\#73](https://github.com/prooph/service-bus/pull/73) ([prolic](https://github.com/prolic))

## [v4.4](https://github.com/prooph/service-bus/tree/v4.4) (2015-09-29)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.3...v4.4)

**Implemented enhancements:**

- update handle command strategy [\#71](https://github.com/prooph/service-bus/pull/71) ([prolic](https://github.com/prolic))

**Merged pull requests:**

- Develop [\#72](https://github.com/prooph/service-bus/pull/72) ([codeliner](https://github.com/codeliner))

## [v4.3](https://github.com/prooph/service-bus/tree/v4.3) (2015-09-13)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.2...v4.3)

**Implemented enhancements:**

- add guard factory tests [\#70](https://github.com/prooph/service-bus/pull/70) ([prolic](https://github.com/prolic))
- Route- & Finalize-Guards [\#69](https://github.com/prooph/service-bus/pull/69) ([prolic](https://github.com/prolic))

**Closed issues:**

- New message dispatcher versions [\#54](https://github.com/prooph/service-bus/issues/54)

## [v4.2](https://github.com/prooph/service-bus/tree/v4.2) (2015-09-08)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.2-beta.1...v4.2)

## [v4.2-beta.1](https://github.com/prooph/service-bus/tree/v4.2-beta.1) (2015-08-31)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.1.2...v4.2-beta.1)

**Implemented enhancements:**

- Add tests [\#68](https://github.com/prooph/service-bus/pull/68) ([prolic](https://github.com/prolic))
- Even more tests + small fix [\#66](https://github.com/prooph/service-bus/pull/66) ([prolic](https://github.com/prolic))
- Add tests [\#64](https://github.com/prooph/service-bus/pull/64) ([prolic](https://github.com/prolic))
- Add tests [\#63](https://github.com/prooph/service-bus/pull/63) ([prolic](https://github.com/prolic))

**Merged pull requests:**

- Add async message producer interface [\#67](https://github.com/prooph/service-bus/pull/67) ([codeliner](https://github.com/codeliner))
- test php7 on travis [\#65](https://github.com/prooph/service-bus/pull/65) ([prolic](https://github.com/prolic))

## [v4.1.2](https://github.com/prooph/service-bus/tree/v4.1.2) (2015-08-21)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.1.1...v4.1.2)

**Merged pull requests:**

- Rename factory namespace to \*Container\* [\#62](https://github.com/prooph/service-bus/pull/62) ([codeliner](https://github.com/codeliner))

## [v4.1.1](https://github.com/prooph/service-bus/tree/v4.1.1) (2015-08-20)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.1...v4.1.1)

## [v4.1](https://github.com/prooph/service-bus/tree/v4.1) (2015-08-20)
[Full Changelog](https://github.com/prooph/service-bus/compare/v4.0...v4.1)

**Implemented enhancements:**

- Decouple MessageDispatch from ZF2\Event [\#48](https://github.com/prooph/service-bus/issues/48)
- Add Command TestCase [\#15](https://github.com/prooph/service-bus/issues/15)
- Implement RESTfulMessageDispatcher as ZF2 Module [\#3](https://github.com/prooph/service-bus/issues/3)

**Closed issues:**

- Update docs for v4.0 [\#52](https://github.com/prooph/service-bus/issues/52)
- Add apigility integration [\#41](https://github.com/prooph/service-bus/issues/41)
- Add rtd documentation [\#22](https://github.com/prooph/service-bus/issues/22)

**Merged pull requests:**

- Cleanup .php\_cs config file [\#61](https://github.com/prooph/service-bus/pull/61) ([prolic](https://github.com/prolic))
- Fixed php-cs for all files in repo [\#59](https://github.com/prooph/service-bus/pull/59) ([prolic](https://github.com/prolic))
- Add php-cs-fixer to travis [\#58](https://github.com/prooph/service-bus/pull/58) ([prolic](https://github.com/prolic))
- Provide container-aware factories [\#57](https://github.com/prooph/service-bus/pull/57) ([codeliner](https://github.com/codeliner))
- Hint message interface [\#56](https://github.com/prooph/service-bus/pull/56) ([codeliner](https://github.com/codeliner))

## [v4.0](https://github.com/prooph/service-bus/tree/v4.0) (2015-08-02)
[Full Changelog](https://github.com/prooph/service-bus/compare/v3.2...v4.0)

**Implemented enhancements:**

- Add a query bus [\#47](https://github.com/prooph/service-bus/issues/47)

**Closed issues:**

- CommandRouter class missed onRouteCommand method. [\#51](https://github.com/prooph/service-bus/issues/51)

**Merged pull requests:**

- Feature/v4.0 [\#55](https://github.com/prooph/service-bus/pull/55) ([codeliner](https://github.com/codeliner))

## [v3.2](https://github.com/prooph/service-bus/tree/v3.2) (2015-05-23)
[Full Changelog](https://github.com/prooph/service-bus/compare/v3.1...v3.2)

**Merged pull requests:**

- Patch 47 - Introduce QueryBus [\#50](https://github.com/prooph/service-bus/pull/50) ([codeliner](https://github.com/codeliner))

## [v3.1](https://github.com/prooph/service-bus/tree/v3.1) (2015-05-09)
[Full Changelog](https://github.com/prooph/service-bus/compare/v3.0.1...v3.1)

## [v3.0.1](https://github.com/prooph/service-bus/tree/v3.0.1) (2015-05-09)
[Full Changelog](https://github.com/prooph/service-bus/compare/v3.0...v3.0.1)

## [v3.0](https://github.com/prooph/service-bus/tree/v3.0) (2015-05-01)
[Full Changelog](https://github.com/prooph/service-bus/compare/v2.0...v3.0)

**Closed issues:**

- Provide php-resque integration with an add on [\#43](https://github.com/prooph/service-bus/issues/43)
- Add SingleHandleMethodInvokeStrategy [\#42](https://github.com/prooph/service-bus/issues/42)

## [v2.0](https://github.com/prooph/service-bus/tree/v2.0) (2015-01-13)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.3.1...v2.0)

**Merged pull requests:**

- Message bus [\#45](https://github.com/prooph/service-bus/pull/45) ([codeliner](https://github.com/codeliner))

## [v1.3.1](https://github.com/prooph/service-bus/tree/v1.3.1) (2015-01-12)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.3.0...v1.3.1)

**Closed issues:**

- DispatchException captures wrong process phase [\#44](https://github.com/prooph/service-bus/issues/44)

## [v1.3.0](https://github.com/prooph/service-bus/tree/v1.3.0) (2014-10-30)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.2.1...v1.3.0)

## [v1.2.1](https://github.com/prooph/service-bus/tree/v1.2.1) (2014-10-30)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.2.0...v1.2.1)

## [v1.2.0](https://github.com/prooph/service-bus/tree/v1.2.0) (2014-10-30)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.1.1...v1.2.0)

## [v1.1.1](https://github.com/prooph/service-bus/tree/v1.1.1) (2014-10-30)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.1.0...v1.1.1)

## [v1.1.0](https://github.com/prooph/service-bus/tree/v1.1.0) (2014-10-05)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.0.2...v1.1.0)

## [v1.0.2](https://github.com/prooph/service-bus/tree/v1.0.2) (2014-10-03)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.0.1...v1.0.2)

## [v1.0.1](https://github.com/prooph/service-bus/tree/v1.0.1) (2014-09-28)
[Full Changelog](https://github.com/prooph/service-bus/compare/v1.0.0...v1.0.1)

## [v1.0.0](https://github.com/prooph/service-bus/tree/v1.0.0) (2014-09-27)
[Full Changelog](https://github.com/prooph/service-bus/compare/v0.4.0...v1.0.0)

## [v0.4.0](https://github.com/prooph/service-bus/tree/v0.4.0) (2014-09-23)
[Full Changelog](https://github.com/prooph/service-bus/compare/0.3.3...v0.4.0)

**Implemented enhancements:**

- Improve event system [\#18](https://github.com/prooph/service-bus/issues/18)

**Fixed bugs:**

- Wrong type hint in InvokeStrategyInterface [\#40](https://github.com/prooph/service-bus/issues/40)
- Improve bus initialization logic [\#39](https://github.com/prooph/service-bus/issues/39)

**Closed issues:**

- Give possibility that messages can provide own factory methods [\#37](https://github.com/prooph/service-bus/issues/37)
- Add possibility to set up message maps after initialization [\#36](https://github.com/prooph/service-bus/issues/36)
- Add error handling of handling errors [\#30](https://github.com/prooph/service-bus/issues/30)
- Add optional message tracking mechanism [\#24](https://github.com/prooph/service-bus/issues/24)

## [0.3.3](https://github.com/prooph/service-bus/tree/0.3.3) (2014-09-07)
[Full Changelog](https://github.com/prooph/service-bus/compare/0.3.2...0.3.3)

## [0.3.2](https://github.com/prooph/service-bus/tree/0.3.2) (2014-07-12)
[Full Changelog](https://github.com/prooph/service-bus/compare/0.3.1...0.3.2)

## [0.3.1](https://github.com/prooph/service-bus/tree/0.3.1) (2014-07-12)
[Full Changelog](https://github.com/prooph/service-bus/compare/0.3.0...0.3.1)

**Fixed bugs:**

- DefaultEventBus does not work with MessageNameProvider [\#38](https://github.com/prooph/service-bus/issues/38)

**Closed issues:**

- Simplify message dispatching [\#31](https://github.com/prooph/service-bus/issues/31)

## [0.3.0](https://github.com/prooph/service-bus/tree/0.3.0) (2014-07-06)
[Full Changelog](https://github.com/prooph/service-bus/compare/0.2.0...0.3.0)

**Implemented enhancements:**

- Change public interface of ServiceBusManager [\#10](https://github.com/prooph/service-bus/issues/10)
- Provide tests for ServiceBusConfiguration [\#1](https://github.com/prooph/service-bus/issues/1)

**Closed issues:**

- Add tests for new message routing [\#35](https://github.com/prooph/service-bus/issues/35)
- Remove config root [\#34](https://github.com/prooph/service-bus/issues/34)
- Reduce configuration to use just one map for commands and one for events [\#33](https://github.com/prooph/service-bus/issues/33)
- Remove queue component [\#32](https://github.com/prooph/service-bus/issues/32)
- Adjust receiver to use new ServiceBus\#route\*To methods [\#29](https://github.com/prooph/service-bus/issues/29)
- Adjust README to reflect new simplicity of direct routing [\#28](https://github.com/prooph/service-bus/issues/28)
- Adjust quickstart to show how easy new direct routing can be used [\#27](https://github.com/prooph/service-bus/issues/27)
- Use services key for configuring the ServiceBusLocator [\#26](https://github.com/prooph/service-bus/issues/26)
- Introduce direct message routing [\#25](https://github.com/prooph/service-bus/issues/25)

## [0.2.0](https://github.com/prooph/service-bus/tree/0.2.0) (2014-07-05)
[Full Changelog](https://github.com/prooph/service-bus/compare/0.1.1...0.2.0)

**Implemented enhancements:**

- Provide FactoryPluginManagers [\#20](https://github.com/prooph/service-bus/issues/20)
- Expand config to configure all ServiceManagers [\#11](https://github.com/prooph/service-bus/issues/11)

## [0.1.1](https://github.com/prooph/service-bus/tree/0.1.1) (2014-07-05)
[Full Changelog](https://github.com/prooph/service-bus/compare/0.1.0...0.1.1)

**Fixed bugs:**

- LocalSynchronousInitializer overrides command map [\#23](https://github.com/prooph/service-bus/issues/23)

## [0.1.0](https://github.com/prooph/service-bus/tree/0.1.0) (2014-06-28)
**Implemented enhancements:**

- Add type to message header [\#17](https://github.com/prooph/service-bus/issues/17)
- Add MessageFactory [\#14](https://github.com/prooph/service-bus/issues/14)
- Change Definition of ServiceBusManager aliases [\#13](https://github.com/prooph/service-bus/issues/13)
- Implement default setup of ServiceBus [\#12](https://github.com/prooph/service-bus/issues/12)
- Implement handling of CommandBus [\#9](https://github.com/prooph/service-bus/issues/9)
- Add EventManager to all components [\#8](https://github.com/prooph/service-bus/issues/8)
- Implement LocalQueueSetup ListenerAggregate [\#7](https://github.com/prooph/service-bus/issues/7)
- Implement InMemoryMessageDispatcher [\#6](https://github.com/prooph/service-bus/issues/6)
- Implement EventReceiver [\#5](https://github.com/prooph/service-bus/issues/5)
- Implement EventBus [\#4](https://github.com/prooph/service-bus/issues/4)
- Implement PhpResqueMessageDispatcher [\#2](https://github.com/prooph/service-bus/issues/2)

**Fixed bugs:**

- Check that DefaultQueueFactory is used in right context [\#19](https://github.com/prooph/service-bus/issues/19)
- Publish events to many EventHandlers [\#16](https://github.com/prooph/service-bus/issues/16)



\* *This Change Log was automatically generated by [github_changelog_generator](https://github.com/skywinder/Github-Changelog-Generator)*
